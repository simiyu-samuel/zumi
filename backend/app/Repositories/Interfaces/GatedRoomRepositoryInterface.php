<?php

namespace App\Repositories\Interfaces;

use App\Models\GatedRoom;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface GatedRoomRepositoryInterface
{
    public function findById(string $id): ?GatedRoom;
    public function getActiveRooms(int $perPage = 15): LengthAwarePaginator;
    public function getByUser(User $user, int $perPage = 15): LengthAwarePaginator;
    public function create(array $data): GatedRoom;
    public function update(GatedRoom $room, array $data): GatedRoom;
    public function delete(GatedRoom $room): bool;
    public function addParticipant(GatedRoom $room, User $user, int $amount): void;
}
