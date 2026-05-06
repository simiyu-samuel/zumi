<?php

namespace App\Services;

use App\Enums\DropsTransactionType;
use App\Enums\GatedRoomStatus;
use App\Models\GatedRoom;
use App\Models\User;
use App\Repositories\Interfaces\GatedRoomRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

use Agence104\LiveKit\AccessToken;
use Agence104\LiveKit\AccessTokenOptions;
use Agence104\LiveKit\VideoGrant;
use Agence104\LiveKit\RoomServiceClient;
use Agence104\LiveKit\RoomCreateOptions;
use Illuminate\Support\Str;

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
        $data['status'] = GatedRoomStatus::Scheduled;
        $data['livekit_room_name'] = 'room_' . Str::random(10);
        
        $room = $this->roomRepository->create($data);

        // Optional: Pre-create room in LiveKit if needed
        // For now, we'll let it be created on first join or when starting.

        return $room;
    }

    public function joinRoom(User $user, GatedRoom $room): array
    {
        try {
            if ($user->id === $room->user_id) {
                return [
                    'success' => true,
                    'token'   => $this->generateJoinToken($user, $room),
                    'is_host' => true,
                ];
            }

            $alreadyJoined = $room->participants()->where('user_id', $user->id)->exists();

            if (!$alreadyJoined) {
                if ($room->entry_fee_drops > 0) {
                    if ($user->drops_balance < $room->entry_fee_drops) {
                        throw new \Exception('Insufficient Drops balance.');
                    }

                    DB::transaction(function () use ($user, $room) {
                        // 1. Transfer funds
                        $this->dropsService->transfer(
                            $user,
                            $room->host,
                            $room->entry_fee_drops,
                            \App\Enums\DropsTransactionType::GatedRoomEntry,
                            $room,
                            [
                                'title' => 'Join Room: ' . $room->title,
                                'debit_description' => "Entered Live Room: {$room->title}",
                                'credit_description' => "Member entry for Room: {$room->title}",
                            ]
                        );

                        // 2. Record participant
                        $this->roomRepository->addParticipant($room, $user, $room->entry_fee_drops);
                    });
                } else {
                    // Free room
                    $this->roomRepository->addParticipant($room, $user, 0);
                }
            }

            return [
                'success' => true,
                'token'   => $this->generateJoinToken($user, $room),
                'is_host' => false,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Generate a LiveKit access token for a room.
     */
    public function generateJoinToken(User $user, GatedRoom $room): string
    {
        $apiKey = config('services.livekit.api_key');
        $apiSecret = config('services.livekit.api_secret');

        if (!$apiKey || !$apiSecret) {
            throw new \RuntimeException('LiveKit credentials not configured.');
        }

        $options = new AccessTokenOptions();
        $options->setIdentity($user->id);
        $options->setName($user->username);

        $token = new AccessToken($apiKey, $apiSecret, $options);

        $grant = new VideoGrant();
        $grant->setRoomJoin(true);
        $grant->setRoomName($room->livekit_room_name);
        
        if ($user->id === $room->user_id) {
            $grant->setRoomCreate(true);
            $grant->setRoomAdmin(true);
        }

        $token->setGrant($grant);
        
        return $token->toJwt();
    }

    public function startRoom(GatedRoom $room): GatedRoom
    {
        return $this->roomRepository->update($room, [
            'status' => GatedRoomStatus::Live,
            'started_at' => now(),
        ]);
    }

    public function endRoom(GatedRoom $room): GatedRoom
    {
        // 1. Close room in LiveKit
        try {
            $client = new RoomServiceClient(
                config('services.livekit.url'),
                config('services.livekit.api_key'),
                config('services.livekit.api_secret')
            );
            $client->deleteRoom($room->livekit_room_name);
        } catch (\Exception $e) {
            // Log error but continue
            \Log::error('Failed to delete LiveKit room: ' . $e->getMessage());
        }

        // 2. Update status
        return $this->roomRepository->update($room, [
            'status' => GatedRoomStatus::Ended,
            'ended_at' => now(),
        ]);
    }
}
