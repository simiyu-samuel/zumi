<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Payout\WithdrawRequest;
use App\Http\Resources\DropsTransactionResource;
use App\Services\PayoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PayoutController extends Controller
{
    public function __construct(
        protected PayoutService $payoutService
    ) {}

    /**
     * Start the Stripe Connect onboarding process.
     */
    public function onboard(Request $request): JsonResponse
    {
        $url = $this->payoutService->getOnboardingLink($request->user());

        return response()->json([
            'onboarding_url' => $url,
        ]);
    }

    /**
     * Withdraw Drops to real USD via Stripe Connect.
     */
    public function withdraw(WithdrawRequest $request): JsonResponse
    {
        $result = $this->payoutService->processWithdrawal(
            $request->user(),
            $request->amount
        );

        if (!$result['success']) {
            return response()->json([
                'message' => $result['message'],
            ], Response::HTTP_BAD_REQUEST);
        }

        return response()->json([
            'message'     => 'Withdrawal successful. Funds transferred to your account.',
            'transaction' => new DropsTransactionResource($result['ledger']),
        ]);
    }
}
