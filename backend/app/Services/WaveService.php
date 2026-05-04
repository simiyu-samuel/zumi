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
        protected CloudflareStreamService $cloudflareStream,
        protected FlowScoreService $flowScoreService,
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

        $wave = $this->waveRepository->create($data);

        // Award Flow Score for posting a Wave
        $this->flowScoreService->award($user, 'wave_posted');

        return $wave;
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

            // Award Flow Score to wave owner (not for self-likes)
            if ($wave->user_id !== $user->id) {
                $waveOwner = $wave->user ?? $wave->load('user')->user;
                $this->flowScoreService->award($waveOwner, 'wave_liked');
            }
        }
    }

    public function purchaseWave(User $user, Wave $wave): array
    {
        try {
            if ($wave->visibility !== WaveVisibility::Gated) {
                throw new \InvalidArgumentException('This Wave is not gated.');
            }

            if ($wave->purchases()->where('user_id', $user->id)->exists()) {
                return ['success' => true]; // Already purchased
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

            return ['success' => true];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function incrementViews(Wave $wave): void
    {
        $wave->increment('views_count');
    }

    public function recordShare(Wave $wave): void
    {
        $wave->increment('shares_count');
    }

    public function toggleBookmark(User $user, Wave $wave): bool
    {
        $existing = $wave->bookmarks()->where('user_id', $user->id)->first();

        if ($existing) {
            $existing->delete();
            return false; // removed bookmark
        }

        $wave->bookmarks()->create(['user_id' => $user->id]);
        return true; // added bookmark
    }

    public function getBookmarks(User $user, int $perPage = 15): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return Wave::whereHas('bookmarks', fn ($q) => $q->where('user_id', $user->id))
            ->with(Wave::DEFAULT_EAGER_LOAD)
            ->latest()
            ->paginate($perPage);
    }
}
