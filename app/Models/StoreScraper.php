<?php

namespace App\Models;

use GuzzleHttp\Client;
use App\Models\Helpers\ScrapeOptimizer;
use App\Models\Helpers\ScrapeState;
use App\Models\Helpers\ScrapeManager;
use Generator;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Pool;
use GuzzleHttp\Promise\Promise;
use GuzzleHttp\Promise\Utils;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Log;
use Iterator;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\DomCrawler\Crawler;
use Illuminate\Support\Arr;

class StoreScraper
{
    protected $optimizer;
    protected $client;
    protected $storeName = '';
    /**
     *  @var  Crawler[] $coupons
     */
    protected array $coupons =  [];
    protected ScrapeManager $mng;
    protected array $postHandle;
    protected array $scrapeTypeClosure;
    protected \Closure $packResult;
    /**
     * @var Promise[] $promises
     */
    protected array $promises;
    

    const LIST = 'list';
    const STORE = 'store';
    const COUPON = 'coupon';
    const SCRAPE_LEVELS = [self::LIST, self::STORE, self::COUPON];
    const SCRAPE_CIRCLE = [self::LIST => self::STORE, self::STORE => self::COUPON, self::COUPON => null];


    public function __construct(
        ScrapeOptimizer $optimizer,
        Client $client,
        ScrapeManager $mng
    ) {
        $this->optimizer    = $optimizer;
        $this->client       = $client;
        $this->mng          = $mng;
    }

    public function init(): self
    {
        $this->scrapeTypeClosure = [
            self::LIST      => [ $this, 'scrapeLinks' ],
            self::STORE     => [ $this, 'scrapeLinks' ],
            self::COUPON    => [ $this, 'scrapeCoupons' ]
        ];

        $this->postHandle = [
            self::LIST      => $this->optimizer->reduceAlphabet(),
            self::STORE     => $this->optimizer->reduceStores(),
            self::COUPON    => $this->optimizer->allCoupons()
        ];

        return $this;
    }

    public function scrapeSource(\Closure $packResult)
    {   
        $this->packResult = $packResult;
        $requests = $this->mng->rejectedRequests(self::LIST);
        
        foreach ($requests as $requestState) {
            $this->scrapeUrl($requestState);            
        }
        Log::debug('scraping done');
    }

    /**
     * return scraped store name
     *
     * @return string
     */
    public function getStoreName(): string
    {
        Log::debug('got store "' . $this->storeName . '"');
        return $this->storeName;
    }

    /**
     * return scraped coupons data
     * through php generator
     * (for optimal iteration)
     *
     * @return Iterator
     */
    public function getCoupons(): Iterator
    {
        $total      = $this->coupons['header']->count();
        Log::debug('coupons count: ' . $total);
        $coupon     = [];
        $arr_keys = array_keys($this->coupons);
        $unpack_text = fn ($i, $k) => $this->coupons[$k]->getNode($i) ? $this->coupons[$k]->getNode($i)->textContent : null;
        
        for ($i = 0; $i < $total; ++$i) {
            foreach ($arr_keys as $key) {
                $coupon[$key] = $unpack_text($i, $key);
            }
            Log::debug('got coupon: ' , $coupon);
            yield $coupon;
        }
    }

    /**
     * scraping by declaritive sraping state
     *
     * @param ScrapeState $state
     * @return void
     */
    public function scrapeUrl(ScrapeState $state): Promise
    {
        $promise = $this->request($state);
        $this->promises[$state->url()] = $promise;
        $promise->wait();
        return $promise;    
    }    
    
    /**
     * async requesting link
     *
     * @param ScrapeState $state
     * @return void
     */
    private function request(ScrapeState $state): Promise
    {
        /**
         *  @var Promise 
         */
        $promise    = $this->client->getAsync($state->url());
        $promise->then(
            fn (ResponseInterface $response)    => $this->handleResponse($response, $state),
            fn (RequestException $e)            => $this->rejected($e, $state)
        );

        return $promise;
    }

    /**
     * response handling
     *
     * @param ResponseInterface $response
     * @param ScrapeState $state
     * @return void
     */
    private function handleResponse(ResponseInterface $response, ScrapeState $state): Generator
    {        
        Log::debug('response success ' . $state->url() . ' ' . $response->getStatusCode());

        $this->mng->requestDone($state);        

        $page               = $response->getBody()->getContents();

        $crawler            = $this->scrapeHtml($state->setHtml($page));

        Log::debug('crawler got dom targets on ' . $state->url(), $state->domElm());

        $nextLinks          = $this->handle($crawler, $state); 

        Log::debug('dom targets reducing to: ' . count($nextLinks), $nextLinks);


        return empty($nextLinks) ?: $this->nextScrape($state->setLinks($nextLinks));        
    }
    
    /**
     * scraping link nodes and reduce their count by closure by state name
     *
     * @param Crawler $crawler
     * @param ScrapeState $state
     * @return array
     */
    private function handle(Crawler $crawler, ScrapeState $state): array
    {
        $links              = [];
        $handler            = $this->postHandle[$state->name()];
        $handler($crawler->count(), $this->packHrefAndContent($crawler, $links));
        return $links;
    }

    /**
     * scrape links and text content from tag <a> and pack in $result variable
     *
     * @param Crawler $crawler
     * @param array &$result
     * @return void
     */
    private function packHrefAndContent(Crawler $crawler, array &$result): \Closure
    {
        return function ($i) use ($crawler, &$result) {
            $node = $crawler->getNode($i);
            $result[$node->textContent] = $node->attributes['href']->value;
        };
    }

    /**
     * scrape links or coupons if it's final page
     *
     * @param ScrapeState $state
     * @return mixed
     */
    private function scrapeHtml(ScrapeState $state): Crawler
    {
        $scrapeMethod        = $this->scrapeTypeClosure[$state->name()];
        return call_user_func($scrapeMethod, $state);        
    }

    private function scrapeLinks(ScrapeState $state): Crawler
    {
        return (new Crawler($state->getHtml()))->filter(Arr::first($state->domElm()));
    }

    private function scrapeCoupons(ScrapeState $state): Crawler
    {
        $this->storeName    = $state->getData('storeName');
        $domTargets         = $state->domElm();
        $coupon             = Arr::pull($domTargets, 'coupon');

        $crawler            = (new Crawler($state->getHtml()))->filter($coupon);

        
        foreach ($domTargets as $prop => $domTarget) {
            $this->coupons[$prop]   = $crawler->filter($domTarget);
        }    
        
        ($this->packResult)($this);

        return $crawler;
    }  

    private function nextScrape(ScrapeState $state): Generator
    {
        $nextStateName = self::SCRAPE_CIRCLE[$state->name()];

        foreach ($state->getLinks() as $content => $link) {
            $this->scrapeUrl($this->mng->stateForUrl($nextStateName, $link, $content));
        }
    }

    private function rejected(RequestException $e, ScrapeState $state)
    {
        if ($e->getResponse()->getStatusCode() == '403') {
            Log::info('response code 403 Forbidden. vpn disabled, turn it on');
            foreach ($this->promises as $promise) {
                $promise->cancel();
            }
            return;
        }
        if ($this->mng->rerunRejectedRequest($state)) {            
            
            Log::info('rejected request rerun on ' . $state->url(), ['msg' => $e->getMessage(), 'state' => $state->name()]);
            return $this->scrapeUrl($state);
        }
        Log::warning('request exception on ' . $state->url(), [ 'msg' =>  $e->getMessage(), 'state' => $state->name()]);      
    }

}
