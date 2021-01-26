<?php
namespace App\Models\Helpers;

use App\Models\Store;
use Iterator;
use App\Models\Coupon;
use App\Models\StoreScraper;
use App\Models\Facades\StoreData;
use Illuminate\Support\Facades\Log;

class ScrapeInDB
{
    public function getData()
    {
        /** @var StoreScraper $storeData */
        $storeData = StoreData::init();
        $storeData->scrapeSource(function (StoreScraper $data) {
            $storeAttr = ['name' => $data->getStoreName()];
            $couponGen = $data->getCoupons();
            /** @var Store $store */
            $store = Store::where($storeAttr)->with('coupons')->first();
            $store = is_null($store) ? $this->newStore($storeAttr, $couponGen) : $this->updateStore($store, $couponGen);
            Log::debug('saved store', array_merge(
                $store->attributesToArray(),
                $store->coupons->map(fn (Coupon $c) => $c->attributesToArray())->toArray()
            ));
        });
    }
    private function newStore(array $storeAttr, Iterator $couponGen): Store
    {
        $store = Store::create($storeAttr);
        $coupons = collect();

        foreach ($couponGen as $couponData) {
            $coupons->add(new Coupon($this->getCouponModelAttr($couponData)));
        }
        return $store->coupons()->saveMany($coupons);
    }

    private function updateStore(Store $store, Iterator $couponGen): Store
    {
        $existCoupons = collect();
        $newCoupons = collect();
        $oldCoupons = $store->coupons;

        foreach ($couponGen as $couponData) {
            $couponAttr = $this->getCouponModelAttr($couponData);
            $existing = $oldCoupons->firstWhere('header', '=', $couponAttr['header']);
            is_null($existing) ? $newCoupons->add(new Coupon($couponAttr)) : $existCoupons->add($existing);
        }

        $save = $store->coupons()->saveMany($newCoupons);

        $toDelete = $oldCoupons->diff($existCoupons->merge($newCoupons));
        $dump = Coupon::whereIn('id', $toDelete->pluck('id'))->delete();
        return $store;
    }

    private function getCouponModelAttr(array $couponData)
    {
        return  [
            'header'    => $couponData['header'],
            'image'     => $couponData['image'],
            'body'      => $couponData['description'],
            'expire'    => $couponData['expire']
        ];
    }
}

