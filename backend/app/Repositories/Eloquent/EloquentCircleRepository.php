<?php

namespace App\Repositories\Eloquent;

use App\Enums\CircleMemberRole;
use App\Models\Circle;
use App\Models\User;
use App\Repositories\Interfaces\CircleRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentCircleRepository implements CircleRepositoryInterface
{
    public function findById(string $id): ?Circle
    {
        return Circle::with(Circle::DEFAULT_EAGER_LOAD)->find($id);
    }

    public function findBySlug(string $slug): ?Circle
    {
        return Circle::with(Circle::DEFAULT_EAGER_LOAD)->where('slug', $slug)->first();
    }

    public function getAll(int $perPage = 15): LengthAwarePaginator
    {
        return Circle::with(Circle::DEFAULT_EAGER_LOAD)->latest()->paginate($perPage);
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

    public function addMember(Circle $circle, User $user, CircleMemberRole $role = CircleMemberRole::Member): void
    {
        $circle->members()->syncWithoutDetaching([
            $user->id => ['id' => \Illuminate\Support\Str::uuid(), 'role' => $role->value]
        ]);
        $circle->increment('members_count');
    }

    public function removeMember(Circle $circle, User $user): void
    {
        if ($circle->members()->detach($user->id)) {
            $circle->decrement('members_count');
        }
    }

    public function getPendingJoinRequests(Circle $circle, int $perPage = 15): LengthAwarePaginator
    {
        return \App\Models\CircleJoinRequest::where('circle_id', $circle->id)
            ->where('status', \App\Enums\CircleJoinRequestStatus::Pending)
            ->with([\App\Models\CircleJoinRequest::RELATION_USER])
            ->paginate($perPage);
    }

    public function findPendingJoinRequest(string $userId, string $circleId): ?\App\Models\CircleJoinRequest
    {
        return \App\Models\CircleJoinRequest::where('user_id', $userId)
            ->where('circle_id', $circleId)
            ->where('status', \App\Enums\CircleJoinRequestStatus::Pending)
            ->first();
    }

    public function createJoinRequest(string $userId, string $circleId): \App\Models\CircleJoinRequest
    {
        return \App\Models\CircleJoinRequest::create([
            'user_id'   => $userId,
            'circle_id' => $circleId,
            'status'    => \App\Enums\CircleJoinRequestStatus::Pending,
        ]);
    }

    public function getMembersJoinedSince(Circle $circle, \Carbon\Carbon $since): int
    {
        return $circle->members()
            ->wherePivot('joined_at', '>=', $since)
            ->count();
    }

    public function getRevenue(Circle $circle, ?\Carbon\Carbon $since = null): float
    {
        $query = \App\Models\DropsLedger::where('reference_type', Circle::class)
            ->where('reference_id', $circle->id)
            ->where('direction', 'credit');

        if ($since) {
            $query->where('created_at', '>=', $since);
        }

        return (float) $query->sum('amount');
    }
}
