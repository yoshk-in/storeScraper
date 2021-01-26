<?php

use App\Models\Helpers\ScrapeOptimizer;

return [
    'source-url' => 'https://www.coupons.com/coupon-codes/stores/',
    'rerunsOnUrl' => 1,
    'storage' => 'scrape_states',
    'stores' => [
        'min' => 20,
        'max' => 50,
        'alphabets' => 2,
        'storesOnList' => 2,
        'mode' => ScrapeOptimizer::SEQUENCE_REDUCING
    ],
    'dom' => [
        'list' => ['.bottom-alphabets > ul > li > a'],
        'store' => ['.view-all-container > .horizontal-list > .column > ul > li > a'],
        'coupon' => [
            'coupon' => '.infinite-scroll-component .coupons-detail-box',
            'image' => '.coupons-product-image > div > div',
            'header' => '.coupons-row-heading > a',
            'description' => '.coupon-description',
            'expire' => '.expire-row > div > span[itemProp="validThrough"]'
        ]
    ],
    'client' => [
        'timeout' => 30,
        // 'proxy' => 'http://188.165.141.114:3129',
        'headers' => [
            'User-Agent' => 'Chrome/18.0.1025.133'
        ]
        // 'ca' => base_path() . 'France-tcp.ovpn'
        // 'cookies' => true
        // 'verify' => false
    ]
];
