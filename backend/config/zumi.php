<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Zumi Platform Configuration
    |--------------------------------------------------------------------------
    */

    'pagination' => [
        'default_per_page' => 15,
        'max_per_page'     => 100,
    ],

    'flow_score' => [
        'tiers' => [
            'rising'  => ['threshold' => 0,     'label' => 'Rising'],
            'flowing' => ['threshold' => 1000,  'label' => 'Flowing'],
            'surging' => ['threshold' => 5000,  'label' => 'Surging'],
            'elite'   => ['threshold' => 15000, 'label' => 'Elite'],
        ],
        'points' => [
            'wave_posted'      => 5,
            'wave_liked'       => 1,
            'comment_received' => 2,
            'follower_gained'  => 10,
            'engagement'       => 1, // Points for the person performing the action (liking/commenting)
            'circle_joined'    => 10,
            'challenge_won'    => 50,
        ],
    ],

    'drops' => [
        'exchange_rate' => 100, // 100 Drops = $1 USD
        'payout' => [
            'min_threshold' => 5000, // $50 USD
            'fee_percentage' => 0,    // Fees are taken on receipt, not payout
            'cooldown_days' => [
                'user'   => 30,
                'pro'    => 30,
                'studio' => 14,
            ],
        ],
        'fees' => [
            // Standard fees by transaction type (§3.5)
            'wave_gift'           => 0.15,
            'skill_drop'          => 0.15,
            'gated_room'          => 0.15,
            'flow_market'         => 0.10,
            'challenge_prize'     => 0.05,
            'circle_subscription' => [
                'pro'     => 0.10,
                'studio'  => 0.07,
                'default' => 0.15,
            ],
            'default' => 0.15,
        ],
    ],

    'waves' => [
        'min_gated_amount' => 100,
    ],

    'subscriptions' => [
        'plans' => [
            'pro' => [
                'price_id' => env('STRIPE_PRO_PRICE_ID') ?: 'price_dummy_pro',
                'name'     => 'Pro',
                'amount'   => 999, // $9.99
            ],
            'studio' => [
                'price_id' => env('STRIPE_STUDIO_PRICE_ID') ?: 'price_dummy_studio',
                'name'     => 'Studio',
                'amount'   => 2499, // $24.99
            ],
        ],
    ],
];
