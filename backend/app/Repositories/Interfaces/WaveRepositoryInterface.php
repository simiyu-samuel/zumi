<?php

namespace App\Repositories\Interfaces;

use App\Models\User;
use App\Models\Wave;
use Illuminate\Contracts\Pagination\CursorPaginator;

interface WaveRepositoryInterface
{
    public function getFeed(int $perPage = 15): CursorPaginator;
    public function getFollowedFeed(User $user, int $perPage = 15): CursorPaginator;
    public function getByUser(string $userId, int $perPage = 15): CursorPaginator;
    public function findById(string $id): ?Wave;
    public function create(array $data): Wave;
    public function update(Wave $wave, array $data): Wave;
    public function delete(Wave $wave): bool;

    // Likes
    public function findLike(Wave $wave, string $userId): ?\App\Models\WaveLike;
    public function addLike(Wave $wave, string $userId): void;
    public function removeLike(Wave $wave, \App\Models\WaveLike $like): void;

    // Bookmarks
    public function findBookmark(Wave $wave, string $userId): ?\App\Models\WaveBookmark;
    public function addBookmark(Wave $wave, string $userId): void;
    public function removeBookmark(Wave $wave, \App\Models\WaveBookmark $bookmark): void;
    public function getBookmarks(User $user, int $perPage = 15): \Illuminate\Contracts\Pagination\LengthAwarePaginator;

    // Purchases
    public function hasPurchased(Wave $wave, string $userId): bool;
    public function addPurchase(Wave $wave, string $userId, int $amountPaid): void;

    // Circle Insights
    public function getTopForCircle(string $circleId, int $limit = 3): \Illuminate\Database\Eloquent\Collection;
}
