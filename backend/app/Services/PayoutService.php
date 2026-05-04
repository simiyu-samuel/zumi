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
     *
     * @throws Exception
     */
    public function processWithdrawal(User $user, int $amount): DropsLedger
    {
        if (!$user->stripe_onboarding_completed) {
            throw new Exception('Please complete Stripe onboarding before withdrawing.');
        }

        if ($user->drops_balance < $amount) {
            throw new Exception('Insufficient Drops balance.');
        }

        return DB::transaction(function () use ($user, $amount) {
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
            // Note: If Stripe fails, the DB transaction will roll back the debit.
            $this->stripeService->transferToConnectedAccount(
                $user->stripe_connect_id,
                $amount // 1 Drop = 1 Cent for conversion
            );

            return $ledger;
        });
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
