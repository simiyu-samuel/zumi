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
        protected MentionService $mentionService,
    ) {}

    public function getDiscoveryFeed(int $perPage = 15): CursorPaginator
    {
        return $this->waveRepository->getFeed($perPage);
    }

    public function getFollowedFeed(User $user, int $perPage = 15): CursorPaginator
    {
        return $this->waveRepository->getFollowedFeed($user, $perPage);
    }

    public function getUserWaves(string $userId, int $perPage = 15): CursorPaginator
    {
        return $this->waveRepository->getByUser($userId, $perPage);
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

        // Process mentions
        $this->mentionService->processMentions($wave, $wave->title . ' ' . ($wave->description ?? ''));

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
        $like = $this->waveRepository->findLike($wave, $user->id);

        if ($like) {
            $this->waveRepository->removeLike($wave, $like);
        } else {
            $this->waveRepository->addLike($wave, $user->id);

            // Award Flow Score and notify wave owner (not for self-likes)
            if ($wave->user_id !== $user->id) {
                $waveOwner = $wave->user ?? $wave->load('user')->user;
                $this->flowScoreService->award($waveOwner, 'wave_liked');
                $this->flowScoreService->award($user, 'engagement');
                $waveOwner->notify(new \App\Notifications\WaveLikedNotification($wave, $user));
            }
        }
    }

    public function purchaseWave(User $user, Wave $wave): array
    {
        try {
            if ($wave->visibility !== WaveVisibility::Gated) {
                throw new \InvalidArgumentException('This Wave is not gated.');
            }

            if ($this->waveRepository->hasPurchased($wave, $user->id)) {
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

                $this->waveRepository->addPurchase($wave, $user->id, $wave->gated_drops);
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
        $existing = $this->waveRepository->findBookmark($wave, $user->id);

        if ($existing) {
            $this->waveRepository->removeBookmark($wave, $existing);
            return false; // removed bookmark
        }

        $this->waveRepository->addBookmark($wave, $user->id);
        return true; // added bookmark
    }

    public function getBookmarks(User $user, int $perPage = 15): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return $this->waveRepository->getBookmarks($user, $perPage);
    }

    public function giftWave(User $sender, Wave $wave, int $amount): array
    {
        $dropsService = app(DropsService::class);
        $result = $dropsService->gift($sender, $wave->user, $amount, $wave);

        if ($result['success']) {
            // Award Flow Score to the giver
            $this->flowScoreService->award($sender, 'wave_gifted');
            
            // Track total gifts on the wave (optional, but good for ranking)
            // $wave->increment('total_gifts_amount', $amount);
        }

        return $result;
    }
}
