<?php

namespace App\Policies;

use App\Models\User;

class DropsPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function viewWallet(User $user): bool
    {
        return true; // Any authenticated user can view their own wallet
    }
}
