<?php

namespace App\Http\Livewire;

use App\Models\Helpers\ScrapeInDB;
use Livewire\Component;
use App\Models\Store;
use Illuminate\Support\Collection;

class StoreList extends Component
{
    /**
     * stores with coupons
     * Store[]
     *
     * @var Collection
     * @var Store[]
     */
    public $stores;
    public $header = [];
    public $image = [];

    protected $rules = [
        'header.*' => 'required',
        'image.*' => 'required'
    ];

    public function mount(): void
    {
        $this->stores = Store::with('coupons')->get();
        // $this->scraper = new ScrapeInDB;
    }

    public function addCoupon(int $index): void
    {
        $this->stores[$index]->coupons()->create([
            'header' => $this->header[$index],
            'image' => $this->image[$index]
        ]);

        $this->header[$index] = '';
        $this->image[$index] = '';
    }

    public function doScrape()
    {
        $scraper = new ScrapeInDB;
        $scraper->getData();
        if (blank($this->stores)) $this->mount();
    }

    public function render()
    {
        return view('livewire.store-list');
    }
}
