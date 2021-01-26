<?php

namespace App\Providers;
use App\Models\Helpers\ScrapeOptimizer;
use App\Models\StoreScraper;
use App\Models\Helpers\ScrapeManager;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class StoreDataServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        App::bind('store_scraper', function () {
            
            $client = new Client(config('scraping.client'));

            $storage = Storage::disk('local');
            $path = config('scraping.storage');

            if (!$storage->exists($path)) {
                $storage->put($path, serialize([]));
                $rejected_scrapes = [];
            } else {
                $rejected_scrapes = unserialize($storage->get($path));
            }
            
            $desctruct_mng = function (array $states) use ($storage, $path) {
                $storage->put($path, serialize($states));
            };

            extract(config('scraping.stores'));

            $scrapingCfg = new ScrapeOptimizer($min, $max, $alphabets, $storesOnList, $mode);

            $scraper = new StoreScraper(
                $scrapingCfg,
                $client,
                new ScrapeManager(config('scraping.source-url'), config('scraping.dom'), config('scraping.rerunsOnUrl'), $rejected_scrapes, $desctruct_mng)
            );
            return $scraper;
        });
    }
}
