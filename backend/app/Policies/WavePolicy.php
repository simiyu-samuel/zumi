<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\User;
use App\Models\Wave;
use Illuminate\Auth\Access\HandlesAuthorization;

class WavePolicy
{
    use HandlesAuthorization;

    public function update(User $user, Wave $wave): bool
    {
        return $user->id === $wave->user_id;
    }

    public function delete(User $user, Wave $wave): bool
    {
        return $user->id === $wave->user_id || $user->hasRole(UserRole::Admin->value);
    }
}
