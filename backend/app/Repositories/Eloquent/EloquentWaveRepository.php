<?php

namespace App\Repositories\Eloquent;

use App\Models\Wave;
use App\Models\WaveLike;
use App\Repositories\Interfaces\WaveRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class EloquentWaveRepository implements WaveRepositoryInterface
{
    public function getFeed(string $type = 'random', int $limit = 10): LengthAwarePaginator
    {
        $query = Wave::with(['user'])->where('visibility', 'public');

        if ($type === 'random') {
            $query->inRandomOrder();
        } else {
            $query->latest();
        }

        return $query->paginate($limit);
    }

    public function create(array $data): Wave
    {
        return Wave::create($data);
    }

    public function findById(int $id): ?Wave
    {
        return Wave::with(['user'])->find($id);
    }

    public function toggleLike(Wave $wave, int $userId): bool
    {
        $like = WaveLike::where('user_id', $userId)->where('wave_id', $wave->id)->first();

        if ($like) {
            $like->delete();
            $wave->decrement('likes_count');
            return false; // Unliked
        }

        WaveLike::create(['user_id' => $userId, 'wave_id' => $wave->id]);
        $wave->increment('likes_count');
        return true; // Liked
    }
}
