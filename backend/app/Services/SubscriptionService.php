<?php

namespace App\Services;

use App\Models\User;
use Exception;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Illuminate\Support\Facades\Config;

class SubscriptionService
{
    /**
     * Create a Stripe Checkout session for a subscription.
     */
    public function createCheckoutSession(User $user, string $planKey): string
    {
        $plan = Config::get("zumi.subscriptions.plans.{$planKey}");

        if (!$plan) {
            throw new BadRequestHttpException("Invalid subscription plan: {$planKey}");
        }

        return $user->newSubscription('default', $plan['price_id'])
            ->checkout([
                'success_url' => config('app.url') . '/settings/billing?success=true',
                'cancel_url'  => config('app.url') . '/settings/billing?canceled=true',
            ])->url;
    }

    /**
     * Swap the user's current subscription to a new plan.
     */
    public function swapPlan(User $user, string $newPlanKey): void
    {
        $plan = Config::get("zumi.subscriptions.plans.{$newPlanKey}");

        if (!$plan) {
            throw new BadRequestHttpException("Invalid subscription plan: {$newPlanKey}");
        }

        if (!$user->subscribed('default')) {
            throw new BadRequestHttpException('User does not have an active subscription to swap.');
        }

        $user->subscription('default')->swap($plan['price_id']);
    }

    /**
     * Cancel the current subscription.
     */
    public function cancelSubscription(User $user): void
    {
        if (!$user->subscribed('default')) {
            throw new BadRequestHttpException('No active subscription found.');
        }

        $user->subscription('default')->cancel();
    }

    /**
     * Resume a cancelled subscription.
     */
    public function resumeSubscription(User $user): void
    {
        if (!$user->subscription('default')?->onGracePeriod()) {
            throw new BadRequestHttpException('Subscription is not on grace period.');
        }

        $user->subscription('default')->resume();
    }

    /**
     * Get the Stripe Billing Portal URL for the user.
     */
    public function getBillingPortalUrl(User $user): string
    {
        return $user->billingPortalUrl(config('app.url') . '/settings/billing');
    }
}
