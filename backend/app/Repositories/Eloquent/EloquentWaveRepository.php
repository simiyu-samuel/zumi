<?php

namespace App\Repositories\Eloquent;

use App\Enums\WaveStatus;
use App\Enums\WaveVisibility;
use App\Models\User;
use App\Models\Wave;
use App\Repositories\Interfaces\WaveRepositoryInterface;
use Illuminate\Contracts\Pagination\CursorPaginator;

class EloquentWaveRepository implements WaveRepositoryInterface
{
    public function getFeed(int $perPage = 15): CursorPaginator
    {
        return Wave::with([Wave::RELATION_USER])
            ->whereIn('visibility', [WaveVisibility::Public, WaveVisibility::Gated])
            ->where('status', WaveStatus::Ready)
            ->latest()
            ->cursorPaginate($perPage);
    }

    public function getFollowedFeed(User $user, int $perPage = 15): CursorPaginator
    {
        $followingIds = $user->following()->pluck('following_id');

        return Wave::with([Wave::RELATION_USER])
            ->whereIn('user_id', $followingIds)
            ->whereIn('visibility', [WaveVisibility::Public, WaveVisibility::Gated])
            ->where('status', WaveStatus::Ready)
            ->latest()
            ->cursorPaginate($perPage);
    }

    public function getByUser(string $userId, int $perPage = 15): CursorPaginator
    {
        return Wave::where('user_id', $userId)
            ->latest()
            ->cursorPaginate($perPage);
    }

    public function findById(string $id): ?Wave
    {
        return Wave::with([Wave::RELATION_USER])->find($id);
    }

    public function create(array $data): Wave
    {
        return Wave::create($data);
    }

    public function update(Wave $wave, array $data): Wave
    {
        $wave->update($data);
        return $wave;
    }

    public function delete(Wave $wave): bool
    {
        return $wave->delete();
    }
}
