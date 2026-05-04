<?php

namespace App\Services;

use App\Enums\DropsTransactionType;
use App\Models\GatedRoom;
use App\Models\User;
use App\Repositories\Interfaces\GatedRoomRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class GatedRoomService
{
    public function __construct(
        protected GatedRoomRepositoryInterface $roomRepository,
        protected DropsService $dropsService
    ) {}

    public function getActiveRooms(int $perPage = 15): LengthAwarePaginator
    {
        return $this->roomRepository->getActiveRooms($perPage);
    }

    public function getUserRooms(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return $this->roomRepository->getByUser($user, $perPage);
    }

    public function createRoom(User $user, array $data): GatedRoom
    {
        $data['user_id'] = $user->id;
        $data['status'] = 'scheduled';
        
        return $this->roomRepository->create($data);
    }

    public function joinRoom(User $user, GatedRoom $room): array
    {
        try {
            if ($user->id === $room->user_id) {
                throw new \Exception('You are the host of this room.');
            }

            if ($room->participants()->where('user_id', $user->id)->exists()) {
                throw new \Exception('You have already joined this room.');
            }

            if ($user->drops_balance < $room->entry_fee_drops) {
                throw new \Exception('Insufficient Drops balance.');
            }

            DB::transaction(function () use ($user, $room) {
                // 1. Transfer funds
                $this->dropsService->transfer(
                    $user,
                    $room->host,
                    $room->entry_fee_drops,
                    DropsTransactionType::Spend,
                    $room,
                    ['title' => 'Join Room: ' . $room->title]
                );

                // 2. Record participant
                $this->roomRepository->addParticipant($room, $user, $room->entry_fee_drops);
            });

            return ['success' => true];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function startRoom(GatedRoom $room): GatedRoom
    {
        return $this->roomRepository->update($room, [
            'status' => 'live',
            'started_at' => now(),
        ]);
    }

    public function endRoom(GatedRoom $room): GatedRoom
    {
        return $this->roomRepository->update($room, [
            'status' => 'ended',
            'ended_at' => now(),
        ]);
    }
}
