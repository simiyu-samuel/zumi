<?php

namespace App\Policies;

use App\Models\SkillDrop;
use App\Models\User;

class SkillDropPolicy
{
    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, SkillDrop $skillDrop): bool
    {
        return $skillDrop->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, SkillDrop $skillDrop): bool
    {
        return $skillDrop->user_id === $user->id;
    }

    /**
     * Determine whether the user can view the content (purchased or owner).
     */
    public function viewContent(User $user, SkillDrop $skillDrop): bool
    {
        return $skillDrop->user_id === $user->id || 
               $skillDrop->buyers()->where('user_id', $user->id)->exists();
    }

    /**
     * Determine whether the user can purchase the model.
     */
    public function purchase(User $user, SkillDrop $skillDrop): bool
    {
        return $skillDrop->user_id !== $user->id && 
               !$skillDrop->buyers()->where('user_id', $user->id)->exists();
    }
}
