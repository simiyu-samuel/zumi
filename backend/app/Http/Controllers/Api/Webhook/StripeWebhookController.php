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

        return response()->json(['status' => 'success']);
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
