<?php

namespace App\Repositories\Interfaces;

use App\Models\Wave;
use Illuminate\Pagination\LengthAwarePaginator;

interface WaveRepositoryInterface
{
    public function getFeed(string $type = 'random', int $limit = 10): LengthAwarePaginator;
    public function create(array $data): Wave;
    public function findById(int $id): ?Wave;
    public function toggleLike(Wave $wave, int $userId): bool;
}
