<?php

namespace App\Services;

use App\Enums\DropsTransactionType;
use App\Enums\WaveStatus;
use App\Enums\WaveVisibility;
use App\Models\Wave;
use App\Models\User;
use App\Repositories\Interfaces\WaveRepositoryInterface;
use Illuminate\Contracts\Pagination\CursorPaginator;

class WaveService
{
    public function __construct(
        protected WaveRepositoryInterface $waveRepository,
        protected CloudflareStreamService $cloudflareStream
    ) {}

    public function getDiscoveryFeed(int $perPage = 15): CursorPaginator
    {
        return $this->waveRepository->getFeed($perPage);
    }

    public function getFollowedFeed(User $user, int $perPage = 15): CursorPaginator
    {
        return $this->waveRepository->getFollowedFeed($user, $perPage);
    }

    /**
     * Initialize a Wave upload by getting a Cloudflare Stream upload URL.
     */
    public function initializeUpload(User $user, int $sizeBytes, array $metadata = []): ?array
    {
        return $this->cloudflareStream->createUploadUrl($sizeBytes, $metadata);
    }

    public function createWave(User $user, array $data): Wave
    {
        $data['user_id'] = $user->id;
        $data['status'] = WaveStatus::Pending;

        return $this->waveRepository->create($data);
    }

    public function getWave(string $id): ?Wave
    {
        return $this->waveRepository->findById($id);
    }

    public function updateWave(Wave $wave, array $data): Wave
    {
        return $this->waveRepository->update($wave, $data);
    }

    public function deleteWave(Wave $wave): bool
    {
        return $this->waveRepository->delete($wave);
    }

    public function likeWave(User $user, Wave $wave): void
    {
        $like = $wave->likes()->where('user_id', $user->id)->first();

        if ($like) {
            $like->delete();
            $wave->decrement('likes_count');
        } else {
            $wave->likes()->create(['user_id' => $user->id]);
            $wave->increment('likes_count');
        }
    }

    public function purchaseWave(User $user, Wave $wave): void
    {
        if ($wave->visibility !== WaveVisibility::Gated) {
            throw new \InvalidArgumentException('This Wave is not gated.');
        }

        if ($wave->purchases()->where('user_id', $user->id)->exists()) {
            return; // Already purchased
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($user, $wave) {
            $dropsService = app(DropsService::class);
            
            $dropsService->transfer(
                $user,
                $wave->user,
                $wave->gated_drops,
                DropsTransactionType::Spend,
                $wave,
                ['title' => $wave->title]
            );

            $wave->purchases()->create([
                'user_id' => $user->id,
                'amount_paid' => $wave->gated_drops,
            ]);
        });
    }

    public function incrementViews(Wave $wave): void
    {
        $wave->increment('views_count');
    }
}
