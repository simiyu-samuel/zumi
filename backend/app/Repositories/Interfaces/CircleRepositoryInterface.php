<?php

namespace App\Repositories\Interfaces;

use App\Models\Circle;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface CircleRepositoryInterface
{
    public function findById(string $id): ?Circle;
    public function findBySlug(string $slug): ?Circle;
    public function getAll(int $perPage = 15): LengthAwarePaginator;
    public function getJoinedByUser(User $user, int $perPage = 15): LengthAwarePaginator;
    public function create(array $data): Circle;
    public function update(Circle $circle, array $data): Circle;
    public function delete(Circle $circle): bool;
    public function addMember(Circle $circle, User $user, string $role = 'member'): void;
    public function removeMember(Circle $circle, User $user): void;
}
