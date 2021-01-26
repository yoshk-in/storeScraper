<?php
namespace App\Models\Helpers;

use App\Models\Helpers\ScrapeDom;
use App\Models\Helpers\ScrapeState;
use Exception;

class ScrapeManager
{
    protected array     $dom;
    protected string    $baseUrl;
    protected ScrapeDom $state;
    protected array     $rejectedRequests = [];
    protected int       $attemptsOnUrl;
    protected \Closure  $destruct;
    

    public function __construct(string $baseUrl, array $domTargets, int $attemptsOnUrl, array $rejected, \Closure $destruct )
    {
        $this->dom      = $domTargets;
        $this->baseUrl  = $baseUrl;
        if ($attemptsOnUrl < 1) throw new Exception('attemps on url must be more then 1');
        $this->attemptsOnUrl = $attemptsOnUrl;
        $this->rejectedRequests = $rejected;
        $this->destruct = $destruct;
    }

    public function stateForUrl(string $stateName, ?string $url = null, ?string $storeName = null): ScrapeState
    {
        return new ScrapeState(
            $url ?? $this->baseUrl,
            $this->dom[$stateName],
            $stateName,
            $storeName ? ['storeName' => $storeName] : []
        );
    }

    public function rerunRejectedRequest(ScrapeState $state): bool
    {
        $state->requestFailed();
        $this->rejectedRequests[$state->url()] = $state;
        return $this->needsRerun($state);
    }

    /**
     *
     * @param string $stateIfNoRejected
     * @return ScrapeState[]
     */
    public function rejectedRequests(string $stateIfNoRejected): array
    {
        return empty($this->rejectedRequests) ? 
            [$this->stateForUrl($stateIfNoRejected)] :
            $this->rejectedRequests;
    }

    public function requestDone(ScrapeState $state): void
    {
        if (isset($this->rejectedRequests[$state->url()])) unset($this->rejectedRequests[$state->url()]);
    }

    private function needsRerun(ScrapeState $state): bool
    {
        return (($state->getFailedCount() + 1) % $this->attemptsOnUrl) !== 0;
    }

    public function __destruct()
    {
        ($this->destruct)($this->rejectedRequests);
    }


}
