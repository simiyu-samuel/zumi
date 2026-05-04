<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\StripeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PurchaseController extends Controller
{
    public function __construct(
        protected StripeService $stripeService
    ) {}

    /**
     * Initiate a Drops purchase.
     */
    public function purchaseDrops(Request $request): JsonResponse
    {
        $request->validate([
            'amount' => 'required|integer|min:100|max:100000', // Min $1, Max $1000
        ]);

        $session = $this->stripeService->createDropsCheckoutSession(
            $request->user(),
            $request->amount
        );

        return response()->json([
            'checkout_url' => $session->url,
            'session_id'   => $session->id,
        ]);
    }
}
