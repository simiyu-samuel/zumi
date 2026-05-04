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
        return Wave::with(Wave::DEFAULT_EAGER_LOAD)
            ->whereIn('visibility', [WaveVisibility::Public, WaveVisibility::Gated])
            ->where('status', WaveStatus::Ready)
            ->latest()
            ->cursorPaginate($perPage);
    }

    public function getFollowedFeed(User $user, int $perPage = 15): CursorPaginator
    {
        $followingIds = $user->following()->pluck('following_id');
        $circleIds = $user->circles()->pluck('circles.id');

        return Wave::with(Wave::DEFAULT_EAGER_LOAD)
            ->where(function ($query) use ($followingIds, $circleIds) {
                $query->whereIn('user_id', $followingIds)
                      ->orWhereIn('circle_id', $circleIds);
            })
            ->whereIn('visibility', [WaveVisibility::Public, WaveVisibility::Gated])
            ->where('status', WaveStatus::Ready)
            ->latest()
            ->cursorPaginate($perPage);
    }

    public function getByUser(string $userId, int $perPage = 15): CursorPaginator
    {
        return Wave::where('user_id', $userId)
            ->with(Wave::DEFAULT_EAGER_LOAD)
            ->latest()
            ->cursorPaginate($perPage);
    }

    public function findById(string $id): ?Wave
    {
        return Wave::with(Wave::DEFAULT_EAGER_LOAD)->find($id);
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

    // Likes

    public function findLike(Wave $wave, string $userId): ?\App\Models\WaveLike
    {
        return $wave->likes()->where('user_id', $userId)->first();
    }

    public function addLike(Wave $wave, string $userId): void
    {
        $wave->likes()->create(['user_id' => $userId]);
        $wave->increment('likes_count');
    }

    public function removeLike(Wave $wave, \App\Models\WaveLike $like): void
    {
        $like->delete();
        $wave->decrement('likes_count');
    }

    // Bookmarks

    public function findBookmark(Wave $wave, string $userId): ?\App\Models\WaveBookmark
    {
        return $wave->bookmarks()->where('user_id', $userId)->first();
    }

    public function addBookmark(Wave $wave, string $userId): void
    {
        $wave->bookmarks()->create(['user_id' => $userId]);
    }

    public function removeBookmark(Wave $wave, \App\Models\WaveBookmark $bookmark): void
    {
        $bookmark->delete();
    }

    public function getBookmarks(User $user, int $perPage = 15): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return Wave::whereHas('bookmarks', fn ($q) => $q->where('user_id', $user->id))
            ->with(Wave::DEFAULT_EAGER_LOAD)
            ->latest()
            ->paginate($perPage);
    }

    // Purchases

    public function hasPurchased(Wave $wave, string $userId): bool
    {
        return $wave->purchases()->where('user_id', $userId)->exists();
    }

    public function addPurchase(Wave $wave, string $userId, int $amountPaid): void
    {
        $wave->purchases()->create([
            'user_id'     => $userId,
            'amount_paid' => $amountPaid,
        ]);
    }

    public function getTopForCircle(string $circleId, int $limit = 3): \Illuminate\Database\Eloquent\Collection
    {
        return Wave::where('circle_id', $circleId)
            ->orderByDesc('views_count')
            ->limit($limit)
            ->get();
    }
}
