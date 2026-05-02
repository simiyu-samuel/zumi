<?php

namespace App\Services;

use App\Models\Wave;
use App\Models\User;
use App\Repositories\Interfaces\WaveRepositoryInterface;
use Illuminate\Contracts\Pagination\CursorPaginator;

class WaveService
{
    public function __construct(
        protected WaveRepositoryInterface $waveRepository
    ) {}

    public function getDiscoveryFeed(int $perPage = 15): CursorPaginator
    {
        return $this->waveRepository->getFeed($perPage);
    }

    public function createWave(User $user, array $data): Wave
    {
        $data['user_id'] = $user->id;

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
}
