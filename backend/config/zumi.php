<?php

return [
    'drops' => [
        'min_payout_amount' => env('ZUMI_MIN_PAYOUT_DROPS', 5000),
        'platform_fee_rate' => env('ZUMI_PLATFORM_FEE_RATE', 0.05),
        'exchange_rate'      => env('ZUMI_DROPS_EXCHANGE_RATE', 100), // 100 Drops = $1.00
    ],
    'waves' => [
        'min_gated_amount'  => env('ZUMI_MIN_GATED_DROPS', 100),
    ],
];
