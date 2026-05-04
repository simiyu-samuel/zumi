<?php

namespace App\Repositories\Eloquent;

use App\Models\GatedRoom;
use App\Models\User;
use App\Repositories\Interfaces\GatedRoomRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class EloquentGatedRoomRepository implements GatedRoomRepositoryInterface
{
    public function findById(string $id): ?GatedRoom
    {
        return GatedRoom::with(GatedRoom::DEFAULT_EAGER_LOAD)->find($id);
    }

    public function getActiveRooms(int $perPage = 15): LengthAwarePaginator
    {
        return GatedRoom::with(GatedRoom::DEFAULT_EAGER_LOAD)
            ->whereIn('status', ['live', 'scheduled'])
            ->latest()
            ->paginate($perPage);
    }

    public function getByUser(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return GatedRoom::with(GatedRoom::DEFAULT_EAGER_LOAD)->where('user_id', $user->id)->latest()->paginate($perPage);
    }

    public function create(array $data): GatedRoom
    {
        return GatedRoom::create($data);
    }

    public function update(GatedRoom $room, array $data): GatedRoom
    {
        $room->update($data);
        return $room->fresh();
    }

    public function delete(GatedRoom $room): bool
    {
        return $room->delete();
    }

    public function addParticipant(GatedRoom $room, User $user, int $amount): void
    {
        $room->participants()->attach($user->id, [
            'id' => (string) Str::uuid(),
            'amount_paid' => $amount,
            'joined_at' => now(),
        ]);
    }
}
