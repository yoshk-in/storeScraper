<?php

namespace App\Http\Controllers;

use App\Models\Helpers\ScrapeInDB;

class MainController extends Controller
{
    public function scrape()
    {
        $storeScraper = new ScrapeInDB;
        $storeScraper->getData();
    }



    public function storeCoupons()
    {        
        return view('storeCoupons');
    }
}
