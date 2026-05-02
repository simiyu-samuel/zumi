<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\DropsLedger;
use Illuminate\Http\Response;

class WalletController extends Controller
{
    /**
     * Get the current user's Drops balance and recent transactions.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $transactions = DropsLedger::where('user_id', $user->id)
            ->latest('created_at')
            ->paginate(20);

        return response()->json([
            'balance'      => $user->drops_balance,
            'transactions' => $transactions,
        ], Response::HTTP_OK);
    }
}
