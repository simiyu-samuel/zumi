<?php

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Support\Collection;

class UserRepository implements UserRepositoryInterface
{
    public function findById(string $id): ?User
    {
        return User::find($id);
    }

    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    public function findByUsername(string $username): ?User
    {
        return User::where('username', $username)->first();
    }

    public function create(array $data): User
    {
        return User::create($data);
    }

    public function update(User $user, array $data): bool
    {
        return $user->update($data);
    }

    public function search(string $query, int $perPage = 15): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return User::where('name', 'like', "%{$query}%")
            ->orWhere('username', 'like', "%{$query}%")
            ->paginate($perPage);
    }

    public function getSuggestedUsers(?string $excludeUserId, int $limit = 5): Collection
    {
        return User::query()
            ->withCount(['followers', 'following'])
            ->where('role', '!=', \App\Enums\UserRole::Admin)
            ->when($excludeUserId, fn($q) => $q->where('id', '!=', $excludeUserId))
            ->when($excludeUserId, function ($q) use ($excludeUserId) {
                // Exclude users already followed
                $q->whereDoesntHave('followers', fn($sub) => $sub->where('follower_id', $excludeUserId));
            })
            ->inRandomOrder()
            ->limit($limit)
            ->get();
    }
}
