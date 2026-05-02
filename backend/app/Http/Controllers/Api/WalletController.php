<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Repositories\Interfaces\DropsRepositoryInterface;
use Illuminate\Http\Response;

class WalletController extends Controller
{
    public function __construct(
        protected DropsRepositoryInterface $dropsRepository
    ) {}

    /**
     * Get the current user's Drops balance and recent transactions.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $transactions = $this->dropsRepository->getForUser($user);

        return response()->json([
            'balance'      => $user->drops_balance,
            'transactions' => $transactions,
        ], Response::HTTP_OK);
    }
}
