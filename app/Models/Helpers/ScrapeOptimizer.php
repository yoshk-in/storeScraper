<?php

namespace App\Models\Helpers;

use Closure;
use Illuminate\Support\Facades\Log;

class ScrapeOptimizer
{
    protected $maxCount;
    protected $minCount;
    protected $count;
    protected $alphabets;
    protected $stores;
    const SEQUENCE_REDUCING = 'sequence';
    const RANDOM_REDUCING = 'reduce';
    const REDUCING_MODE = [
        'sequence' => 'sequence',
        'random' => 'random'
    ];
    protected string $mode;

    public function __construct($min, $max, $alphabets = 20, $storesOnList = 2, $mode = 'sequence')
    {
        $this->minCount = $min;
        $this->maxCount = $max;
        $this->count = ($max + $min) / 2;
        $this->alphabets = $alphabets;
        $this->stores = $storesOnList;
        $this->mode = $mode;
    }

    public function reduceAlphabet(): Closure
    {
        return $this->reduceSelection($this->alphabets);
    }


    public function reduceStores(): Closure
    {
        return $this->reduceSelection($this->stores);
    }

    public function allCoupons(): Closure
    {
        return fn () => null;
    }

    public function reduceSelection(int $count)
    {
        return function (int $total, \Closure $onIndexHandle) use ($count) {
            $reduce_fnc = self::REDUCING_MODE[$this->mode];
            $this->{$reduce_fnc}($total, $count, $onIndexHandle);
        };
    }

    protected function sequence(int $total, int $count, \Closure $onIndexHandle)
    {
        $interval = $total / $count;
        $interval = round($interval);

        Log::debug(self::class . '::' . __FUNCTION__ . " reducing from $total to $count count; interval $interval");

        for ($i = 0; $i < $total; $i = $i + $interval) {
            Log::debug("i: $i; ");
            $onIndexHandle($i);
        }
    }

    protected function random(int $total, int $count, \Closure $onIndexHandle)
    {
        Log::debug(self::class . '::' . __FUNCTION__ . " reducing from $total to $count count");

        $selects = 0;

        while ($selects === $count) {
            $i = mt_rand(0, $total);

            Log::debug("i: $i; ");
            
            $onIndexHandle($i);
            ++$selects;
        }
    }


}
