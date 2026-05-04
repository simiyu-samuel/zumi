<?php

namespace App\Repositories\Eloquent;

use App\Models\Circle;
use App\Models\User;
use App\Repositories\Interfaces\CircleRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentCircleRepository implements CircleRepositoryInterface
{
    public function findById(string $id): ?Circle
    {
        return Circle::find($id);
    }

    public function findBySlug(string $slug): ?Circle
    {
        return Circle::where('slug', $slug)->first();
    }

    public function getAll(int $perPage = 15): LengthAwarePaginator
    {
        return Circle::latest()->paginate($perPage);
    }

    public function getJoinedByUser(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return $user->circles()->latest('joined_at')->paginate($perPage);
    }

    public function create(array $data): Circle
    {
        return Circle::create($data);
    }

    public function update(Circle $circle, array $data): Circle
    {
        $circle->update($data);
        return $circle;
    }

    public function delete(Circle $circle): bool
    {
        return $circle->delete();
    }

    public function addMember(Circle $circle, User $user, string $role = 'member'): void
    {
        $circle->members()->syncWithoutDetaching([
            $user->id => ['id' => \Illuminate\Support\Str::uuid(), 'role' => $role]
        ]);
        $circle->increment('members_count');
    }

    public function removeMember(Circle $circle, User $user): void
    {
        if ($circle->members()->detach($user->id)) {
            $circle->decrement('members_count');
        }
    }
}
