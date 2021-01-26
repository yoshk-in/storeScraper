<?php

namespace App\Exceptions;

use App\Models\StoreScraper;
use Exception;
use GuzzleHttp\Exception\RequestException;

class ScrapeConnectionException extends Exception
{
    protected StoreScraper $scraper;
    protected RequestException $guzzleException;

    public static function throw(StoreScraper $scraper, RequestException $e)
    {
        $exception = new static();
        $exception->scraper = $scraper;
        $exception->guzzleException = $e;
        throw $exception;
    }


}
