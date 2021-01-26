<?php
namespace App\Models\Facades;

use Illuminate\Support\Facades\Facade;


class StoreData extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'store_scraper';
    }
}

