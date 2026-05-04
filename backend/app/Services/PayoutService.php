<?php

namespace App\Services;

use App\Enums\DropsTransactionType;
use App\Models\User;
use App\Models\DropsLedger;
use Exception;
use Illuminate\Support\Facades\DB;

class PayoutService
{
    public function __construct(
        protected StripeService $stripeService,
        protected DropsService $dropsService
    ) {}

    /**
     * Process a withdrawal from Drops to Stripe Connect.
     */
    public function processWithdrawal(User $user, int $amount): array
    {
        try {
            if (!$user->stripe_onboarding_completed) {
                throw new Exception('Please complete Stripe onboarding before withdrawing.');
            }

            $minThreshold = config('zumi.drops.payout.min_threshold', 5000);
            if ($amount < $minThreshold) {
                throw new Exception("Minimum withdrawal amount is {$minThreshold} Drops.");
            }

            if ($user->drops_balance < $amount) {
                throw new Exception('Insufficient Drops balance.');
            }

            // Check payout cooldown based on role
            $roleValue = $user->role instanceof \UnitEnum ? $user->role->value : $user->role;
            $cooldownDays = config("zumi.drops.payout.cooldown_days.{$roleValue}", 30);
            
            $lastPayout = $user->dropsLedger()
                ->where('type', DropsTransactionType::Payout)
                ->latest()
                ->first();

            if ($lastPayout && $lastPayout->created_at->addDays($cooldownDays)->isFuture()) {
                $availableAt = $lastPayout->created_at->addDays($cooldownDays)->diffForHumans();
                throw new Exception("Payout schedule restriction. Next withdrawal available {$availableAt}.");
            }

            $ledger = DB::transaction(function () use ($user, $amount) {
                // 1. Debit the Drops balance
                $ledger = $this->dropsService->debit(
                    $user,
                    $amount,
                    DropsTransactionType::Payout,
                    null,
                    null,
                    ['stripe_connect_id' => $user->stripe_connect_id]
                );

                // 2. Perform the transfer in Stripe
                // 1 Drop = (amount / exchange_rate) dollars.
                // transferToConnectedAccount expects cents.
                // If 100 Drops = $1.00 (100 cents), then amountInCents = amount.
                $exchangeRate = config('zumi.drops.exchange_rate', 100);
                $amountInCents = (int) ($amount * (100 / $exchangeRate));

                $this->stripeService->transferToConnectedAccount(
                    $user->stripe_connect_id,
                    $amountInCents
                );

                return $ledger;
            });

            return [
                'success' => true,
                'ledger'  => $ledger,
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get or create a Connect account and return an onboarding link.
     */
    public function getOnboardingLink(User $user): string
    {
        if (!$user->stripe_connect_id) {
            $account = $this->stripeService->createConnectAccount($user);
            $user->update(['stripe_connect_id' => $account->id]);
        }

        $link = $this->stripeService->createAccountLink($user->stripe_connect_id);

        return $link->url;
    }
}
