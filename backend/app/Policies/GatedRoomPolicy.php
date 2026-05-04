<?php

namespace App\Policies;

use App\Enums\GatedRoomStatus;
use App\Models\GatedRoom;
use App\Models\User;

class GatedRoomPolicy
{
    /**
     * Determine whether the user can create gated rooms.
     */
    public function create(User $user): bool
    {
        return $user->can('host gated rooms') || $user->isPremium();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, GatedRoom $room): bool
    {
        return $room->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, GatedRoom $room): bool
    {
        return $room->user_id === $user->id;
    }

    /**
     * Determine whether the user can join the room.
     */
    public function join(User $user, GatedRoom $room): bool
    {
        // Host can always join to get token
        if ($room->user_id === $user->id) {
            return true;
        }

        // If room has ended, only allow if replay is enabled
        if ($room->status === GatedRoomStatus::Ended) {
            return $room->is_replay_enabled;
        }

        return true;
    }

    /**
     * Determine whether the user can view the room (host or participant).
     */
    public function view(User $user, GatedRoom $room): bool
    {
        return $room->user_id === $user->id || 
               $room->participants()->where('user_id', $user->id)->exists();
    }
}
