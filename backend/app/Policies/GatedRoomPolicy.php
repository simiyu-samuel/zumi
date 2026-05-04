<?php

namespace App\Policies;

use App\Models\GatedRoom;
use App\Models\User;

class GatedRoomPolicy
{
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
        return $room->user_id !== $user->id && 
               $room->status !== 'ended' &&
               !$room->participants()->where('user_id', $user->id)->exists();
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
