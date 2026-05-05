<?php

namespace App\Repositories\Interfaces;

use App\Models\User;
use Illuminate\Support\Collection;

interface UserRepositoryInterface
{
    public function findById(string $id): ?User;
    public function findByEmail(string $email): ?User;
    public function findByUsername(string $username): ?User;
    public function create(array $data): User;
    public function update(User $user, array $data): bool;
    public function search(string $query, int $perPage = 15): \Illuminate\Contracts\Pagination\LengthAwarePaginator;
    public function getSuggestedUsers(?string $excludeUserId, int $limit = 5): Collection;
}
