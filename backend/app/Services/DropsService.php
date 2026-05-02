<?php

namespace App\Services;

use App\Enums\DropsTransactionDirection;
use App\Enums\DropsTransactionStatus;
use App\Models\DropsLedger;
use App\Models\User;
use App\Repositories\Interfaces\DropsRepositoryInterface;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class DropsService
{
    public function __construct(
        protected DropsRepositoryInterface $dropsRepository
    ) {}

    /**
     * Credit Drops to a user.
     */
    public function credit(User $user, int $amount, string $type, ?string $referenceType = null, ?string $referenceId = null, array $metadata = []): DropsLedger
    {
        if ($amount <= 0) {
            throw new InvalidArgumentException('Credit amount must be positive.');
        }

        return DB::transaction(function () use ($user, $amount, $type, $referenceType, $referenceId, $metadata) {
            $ledger = $this->dropsRepository->create([
                'user_id'        => $user->id,
                'type'           => $type,
                'amount'         => $amount,
                'direction'      => DropsTransactionDirection::Credit,
                'reference_type' => $referenceType,
                'reference_id'   => $referenceId,
                'status'         => DropsTransactionStatus::Completed,
                'metadata'       => $metadata,
            ]);

            $user->increment('drops_balance', $amount);

            return $ledger;
        });
    }

    /**
     * Debit Drops from a user.
     */
    public function debit(User $user, int $amount, string $type, ?string $referenceType = null, ?string $referenceId = null, array $metadata = []): DropsLedger
    {
        if ($amount <= 0) {
            throw new InvalidArgumentException('Debit amount must be positive.');
        }

        if ($user->drops_balance < $amount) {
            throw new InvalidArgumentException('Insufficient Drops balance.');
        }

        return DB::transaction(function () use ($user, $amount, $type, $referenceType, $referenceId, $metadata) {
            $ledger = $this->dropsRepository->create([
                'user_id'        => $user->id,
                'type'           => $type,
                'amount'         => $amount,
                'direction'      => DropsTransactionDirection::Debit,
                'reference_type' => $referenceType,
                'reference_id'   => $referenceId,
                'status'         => DropsTransactionStatus::Completed,
                'metadata'       => $metadata,
            ]);

            $user->decrement('drops_balance', $amount);

            return $ledger;
        });
    }

    /**
     * Get the real-time balance by summing the ledger (Audit helper).
     */
    public function calculateBalance(User $user): int
    {
        return $this->dropsRepository->calculateBalance($user);
    }
}
