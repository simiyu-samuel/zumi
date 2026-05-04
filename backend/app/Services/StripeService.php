<?php

namespace App\Services;

use App\Models\User;
use Stripe\Checkout\Session;
use Stripe\Stripe;

class StripeService
{
    public function __construct()
    {
        Stripe::setApiKey(config('cashier.secret'));
    }

    /**
     * Create a Stripe Checkout Session for purchasing Drops.
     * 100 Drops = $1.00 USD
     */
    public function createDropsCheckoutSession(User $user, int $dropsAmount): object
    {
        $amountInCents = $dropsAmount; // Since 100 Drops = 100 Cents ($1)

        return Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => [
                        'name' => "{$dropsAmount} Zumi Drops",
                        'description' => "Purchase of {$dropsAmount} virtual Drops for the Zumi platform.",
                    ],
                    'unit_amount' => 1, // 1 cent per drop
                ],
                'quantity' => $dropsAmount,
            ]],
            'mode' => 'payment',
            'success_url' => config('app.url') . '/wallet?success=true',
            'cancel_url'  => config('app.url') . '/wallet?canceled=true',
            'customer_email' => $user->email,
            'metadata' => [
                'user_id'      => $user->id,
                'drops_amount' => $dropsAmount,
                'type'         => 'drops_purchase',
            ],
        ]);
    }
}
