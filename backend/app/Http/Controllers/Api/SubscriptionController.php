<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Subscription\ChangePlanRequest;
use App\Http\Requests\Subscription\SubscribeRequest;
use App\Http\Resources\PlanResource;
use App\Http\Resources\SubscriptionResource;
use App\Repositories\Interfaces\SubscriptionRepositoryInterface;
use App\Services\SubscriptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function __construct(
        protected SubscriptionService $subscriptionService,
        protected SubscriptionRepositoryInterface $subscriptionRepository
    ) {}

    /**
     * List available subscription plans and current user status.
     */
    public function index(Request $request): JsonResponse
    {
        $plans = $this->subscriptionRepository->getAvailablePlans();
        $subscription = $this->subscriptionRepository->getActiveForUser($request->user());

        return response()->json([
            'plans' => PlanResource::collection($plans),
            'current_subscription' => new SubscriptionResource($subscription),
        ]);
    }

    /**
     * Initiate a subscription via Stripe Checkout.
     */
    public function subscribe(SubscribeRequest $request): JsonResponse
    {
        $this->authorize('subscribe', 'subscription');

        $url = $this->subscriptionService->createCheckoutSession(
            $request->user(),
            $request->validated('plan')
        );

        return response()->json(['checkout_url' => $url]);
    }

    /**
     * Change the current subscription plan.
     */
    public function change(ChangePlanRequest $request): JsonResponse
    {
        $this->authorize('change', 'subscription');

        $this->subscriptionService->swapPlan(
            $request->user(),
            $request->validated('plan')
        );

        return response()->json(['message' => 'Subscription updated successfully.']);
    }

    /**
     * Cancel the current subscription.
     */
    public function cancel(Request $request): JsonResponse
    {
        $this->authorize('cancel', 'subscription');

        $this->subscriptionService->cancelSubscription($request->user());

        return response()->json(['message' => 'Subscription cancelled successfully.']);
    }

    /**
     * Resume a cancelled subscription.
     */
    public function resume(Request $request): JsonResponse
    {
        $this->authorize('resume', 'subscription');

        $this->subscriptionService->resumeSubscription($request->user());

        return response()->json(['message' => 'Subscription resumed successfully.']);
    }

    /**
     * Get the billing portal URL.
     */
    public function portal(Request $request): JsonResponse
    {
        $url = $this->subscriptionService->getBillingPortalUrl($request->user());

        return response()->json(['portal_url' => $url]);
    }
}
