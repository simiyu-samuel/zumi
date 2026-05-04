<?php

namespace App\Repositories\Interfaces;

use App\Models\SkillDrop;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface SkillDropRepositoryInterface
{
    public function findById(string $id): ?SkillDrop;
    public function findBySlug(string $slug): ?SkillDrop;
    public function getAll(int $perPage = 15): LengthAwarePaginator;
    public function getByUser(User $user, int $perPage = 15): LengthAwarePaginator;
    public function getPurchasedByUser(User $user, int $perPage = 15): LengthAwarePaginator;
    public function create(array $data): SkillDrop;
    public function update(SkillDrop $skillDrop, array $data): SkillDrop;
    public function delete(SkillDrop $skillDrop): bool;
    public function recordPurchase(SkillDrop $skillDrop, User $user, int $amount): void;
}
