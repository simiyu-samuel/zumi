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
}
