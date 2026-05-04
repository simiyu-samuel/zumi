<?php

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Repositories\Interfaces\SubscriptionRepositoryInterface;
use Illuminate\Support\Facades\Config;

class EloquentSubscriptionRepository implements SubscriptionRepositoryInterface
{
    /**
     * Get the active subscription for a user.
     */
    public function getActiveForUser(User $user)
    {
        return $user->subscription('default');
    }

    /**
     * Get available plans.
     */
    public function getAvailablePlans(): array
    {
        $plans = Config::get('zumi.subscriptions.plans', []);
        
        $formatted = [];
        foreach ($plans as $key => $data) {
            $formatted[] = array_merge($data, ['key' => $key]);
        }

        return $formatted;
    }
}
