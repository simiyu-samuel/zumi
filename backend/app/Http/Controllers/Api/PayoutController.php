<?php

namespace App\Http\Controllers\Api;

use App\Enums\DropsTransactionType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Payout\WithdrawRequest;
use App\Http\Resources\DropsTransactionResource;
use App\Services\DropsService;
use App\Services\StripeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PayoutController extends Controller
{
    public function __construct(
        protected StripeService $stripeService,
        protected DropsService $dropsService
    ) {}

    /**
     * Start the Stripe Connect onboarding process.
     */
    public function onboard(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->stripe_connect_id) {
            $account = $this->stripeService->createConnectAccount($user);
            $user->update(['stripe_connect_id' => $account->id]);
        }

        $link = $this->stripeService->createAccountLink($user->stripe_connect_id);

        return response()->json([
            'onboarding_url' => $link->url,
        ]);
    }

    /**
     * Withdraw Drops to real USD via Stripe Connect.
     * Minimum 5,000 Drops.
     */
    public function withdraw(WithdrawRequest $request): JsonResponse
    {
        $user = $request->user();
        $dropsAmount = $request->amount;

        if (!$user->stripe_onboarding_completed) {
            return response()->json([
                'message' => 'Please complete Stripe onboarding before withdrawing.',
            ], Response::HTTP_BAD_REQUEST);
        }

        if ($user->drops_balance < $dropsAmount) {
            return response()->json([
                'message' => 'Insufficient Drops balance.',
            ], Response::HTTP_BAD_REQUEST);
        }

        // Convert Drops to Cents (100 Drops = 100 Cents = $1.00)
        $amountInCents = $dropsAmount;

        try {
            // 1. Debit the Drops balance first
            $ledger = $this->dropsService->debit(
                $user,
                $dropsAmount,
                DropsTransactionType::Payout,
                null,
                null,
                ['stripe_connect_id' => $user->stripe_connect_id]
            );

            // 2. Perform the transfer in Stripe
            $this->stripeService->transferToConnectedAccount(
                $user->stripe_connect_id,
                $amountInCents
            );

            return response()->json([
                'message'     => 'Withdrawal successful. Funds transferred to your account.',
                'transaction' => new DropsTransactionResource($ledger),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Withdrawal failed: ' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
