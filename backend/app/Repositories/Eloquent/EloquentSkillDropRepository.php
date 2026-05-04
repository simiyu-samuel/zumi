<?php

namespace App\Repositories\Eloquent;

use App\Models\SkillDrop;
use App\Models\User;
use App\Repositories\Interfaces\SkillDropRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class EloquentSkillDropRepository implements SkillDropRepositoryInterface
{
    public function findById(string $id): ?SkillDrop
    {
        return SkillDrop::with(SkillDrop::DEFAULT_EAGER_LOAD)->find($id);
    }

    public function findBySlug(string $slug): ?SkillDrop
    {
        return SkillDrop::with(SkillDrop::DEFAULT_EAGER_LOAD)->where('slug', $slug)->first();
    }

    public function getAll(int $perPage = 15): LengthAwarePaginator
    {
        return SkillDrop::with(SkillDrop::DEFAULT_EAGER_LOAD)->latest()->paginate($perPage);
    }

    public function getByUser(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return SkillDrop::with(SkillDrop::DEFAULT_EAGER_LOAD)->where('user_id', $user->id)->latest()->paginate($perPage);
    }

    public function getPurchasedByUser(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return $user->purchasedSkillDrops()->with(SkillDrop::DEFAULT_EAGER_LOAD)->latest('purchased_at')->paginate($perPage);
    }

    public function create(array $data): SkillDrop
    {
        return SkillDrop::create($data);
    }

    public function update(SkillDrop $skillDrop, array $data): SkillDrop
    {
        $skillDrop->update($data);
        return $skillDrop->fresh();
    }

    public function delete(SkillDrop $skillDrop): bool
    {
        return $skillDrop->delete();
    }

    public function recordPurchase(SkillDrop $skillDrop, User $user, int $amount): void
    {
        $skillDrop->buyers()->attach($user->id, [
            'id' => (string) Str::uuid(),
            'amount_paid' => $amount,
            'purchased_at' => now(),
        ]);
        
        $skillDrop->increment('sales_count');
    }
}
