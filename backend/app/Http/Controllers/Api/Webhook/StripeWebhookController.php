<?php

namespace App\Http\Controllers\Api\Webhook;

use App\Enums\DropsTransactionType;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\DropsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Webhook;

class StripeWebhookController extends Controller
{
    public function __construct(
        protected DropsService $dropsService
    ) {}

    public function handle(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $secret = config('cashier.webhook.secret') ?? config('services.stripe.webhook_secret');

        try {
            if (app()->environment('testing')) {
                $event = json_decode($payload);
            } else {
                $event = Webhook::constructEvent($payload, $sigHeader, $secret);
            }
        } catch (\UnexpectedValueException $e) {
            return response()->json(['error' => 'Invalid payload'], 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;
            $this->handleDropsPurchase($session);
        }

        if ($event->type === 'account.updated') {
            $account = $event->data->object;
            $this->handleAccountUpdate($account);
        }

        if (in_array($event->type, ['customer.subscription.created', 'customer.subscription.updated', 'customer.subscription.deleted'])) {
            $subscription = $event->data->object;
            $this->handleSubscriptionSync($subscription);
        }

        return response()->json(['status' => 'success']);
    }

    protected function handleSubscriptionSync($stripeSubscription)
    {
        $user = User::where('stripe_id', $stripeSubscription->customer)->first();

        if (!$user) {
            return;
        }

        if ($stripeSubscription->status !== 'active' && $stripeSubscription->status !== 'trialing') {
            $user->update(['role' => \App\Enums\UserRole::User]);
            return;
        }

        $priceId = $stripeSubscription->items->data[0]->price->id;
        $plans = config('zumi.subscriptions.plans');

        if ($priceId === ($plans['studio']['price_id'] ?? null)) {
            $user->update(['role' => \App\Enums\UserRole::Studio]);
        } elseif ($priceId === ($plans['pro']['price_id'] ?? null)) {
            $user->update(['role' => \App\Enums\UserRole::Pro]);
        } else {
            // Default to User if price doesn't match Pro/Studio
            $user->update(['role' => \App\Enums\UserRole::User]);
        }
    }

    protected function handleAccountUpdate($account)
    {
        if ($account->details_submitted && $account->charges_enabled && $account->payouts_enabled) {
            $user = User::where('stripe_connect_id', $account->id)->first();
            if ($user) {
                $user->update(['stripe_onboarding_completed' => true]);
                Log::info("User {$user->id} completed Stripe onboarding.");
            }
        }
    }

    protected function handleDropsPurchase($session)
    {
        $metadata = $session->metadata;

        if (!isset($metadata->type) || $metadata->type !== 'drops_purchase') {
            return;
        }

        $userId = $metadata->user_id;
        $dropsAmount = (int) $metadata->drops_amount;

        $user = User::find($userId);

        if ($user) {
            $this->dropsService->credit(
                $user,
                $dropsAmount,
                DropsTransactionType::Purchase,
                null,
                null,
                ['stripe_session_id' => $session->id]
            );
            
            Log::info("Drops credited via Stripe: User {$userId}, Amount {$dropsAmount}");
        }
    }
}
