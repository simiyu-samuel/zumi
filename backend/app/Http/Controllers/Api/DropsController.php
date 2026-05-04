<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Drops\GiftDropsRequest;
use App\Http\Resources\DropsTransactionResource;
use App\Models\User;
use App\Services\DropsService;
use App\Repositories\Interfaces\DropsRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DropsController extends Controller
{
    public function __construct(
        protected DropsService $dropsService,
        protected DropsRepositoryInterface $dropsRepository
    ) {}

    /**
     * Get wallet balance and transaction history.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $transactions = $this->dropsRepository->getForUser($user);

        return response()->json([
            'balance'      => $user->drops_balance,
            'transactions' => DropsTransactionResource::collection($transactions)->response()->getData(true),
        ]);
    }

    /**
     * Gift Drops to another user.
     */
    public function gift(GiftDropsRequest $request): JsonResponse
    {
        $sender = $request->user();
        $receiver = User::findOrFail($request->receiver_id);
        
        $reference = null;
        if ($request->reference_type && $request->reference_id) {
            $reference = $request->reference_type::findOrFail($request->reference_id);
        }

        $result = $this->dropsService->gift($sender, $receiver, $request->amount, $reference);

        if (!$result['success']) {
            return response()->json(['message' => $result['message']], Response::HTTP_BAD_REQUEST);
        }

        return response()->json([
            'message'         => "Successfully gifted {$request->amount} Drops to {$receiver->username}.",
            'current_balance' => $sender->refresh()->drops_balance,
        ]);
    }
}
