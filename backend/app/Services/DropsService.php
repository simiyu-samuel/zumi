<?php

namespace App\Services;

use App\Enums\DropsTransactionDirection;
use App\Enums\DropsTransactionStatus;
use App\Enums\DropsTransactionType;
use App\Enums\UserRole;
use App\Models\DropsLedger;
use App\Models\User;
use App\Repositories\Interfaces\DropsRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
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
    public function credit(User $user, int $amount, string|DropsTransactionType $type, ?string $referenceType = null, ?string $referenceId = null, array $metadata = []): DropsLedger
    {
        $type = $type instanceof DropsTransactionType ? $type->value : $type;

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
    public function debit(User $user, int $amount, string|DropsTransactionType $type, ?string $referenceType = null, ?string $referenceId = null, array $metadata = []): DropsLedger
    {
        $type = $type instanceof DropsTransactionType ? $type->value : $type;

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
     * Transfer Drops from one user to another (handles platform fees).
     */
    public function transfer(User $sender, User $receiver, int $amount, DropsTransactionType $type, ?Model $reference = null, array $metadata = []): void
    {
        if ($sender->id === $receiver->id) {
            throw new InvalidArgumentException('You cannot transfer Drops to yourself.');
        }

        DB::transaction(function () use ($sender, $receiver, $amount, $type, $reference, $metadata) {
            $referenceType = $reference ? get_class($reference) : null;
            $referenceId = $reference ? $reference->id : null;

            // 1. Debit sender (Full amount)
            $this->debit(
                $sender, 
                $amount, 
                $type, 
                $referenceType, 
                $referenceId, 
                array_merge($metadata, ['receiver_id' => $receiver->id])
            );

            // 2. Calculate platform fee
            $feeAmount = $this->calculatePlatformFee($receiver, $amount);
            $netAmount = $amount - $feeAmount;

            // 3. Credit receiver (Net amount)
            $this->credit(
                $receiver, 
                $netAmount, 
                $type, 
                $referenceType, 
                $referenceId, 
                array_merge($metadata, ['sender_id' => $sender->id, 'gross_amount' => $amount, 'fee_deducted' => $feeAmount])
            );

            // 4. Record fee in platform ledger (if any)
            if ($feeAmount > 0) {
                $this->dropsRepository->create([
                    'user_id'        => null,
                    'type'           => DropsTransactionType::Fee->value,
                    'amount'         => $feeAmount,
                    'direction'      => DropsTransactionDirection::Credit,
                    'reference_type' => $referenceType,
                    'reference_id'   => $referenceId,
                    'status'         => DropsTransactionStatus::Completed,
                    'metadata'       => array_merge($metadata, ['sender_id' => $sender->id, 'receiver_id' => $receiver->id, 'gross_amount' => $amount]),
                ]);
            }
        });
    }

    /**
     * Gift Drops from one user to another.
     */
    public function gift(User $sender, User $receiver, int $amount, ?Model $reference = null): void
    {
        $this->transfer($sender, $receiver, $amount, DropsTransactionType::Gift, $reference);
    }

    /**
     * Calculate platform fee based on the user's role/plan.
     */
    public function calculatePlatformFee(User $user, int $amount): int
    {
        $rate = match ($user->role) {
            UserRole::Studio => 0.07,
            UserRole::Pro    => 0.10,
            default          => 0.15,
        };

        return (int) floor($amount * $rate);
    }

    /**
     * Get the real-time balance by summing the ledger (Audit helper).
     */
    public function calculateBalance(User $user): int
    {
        return $this->dropsRepository->calculateBalance($user);
    }
}
