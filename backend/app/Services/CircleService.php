<?php

namespace App\Services;

use App\Enums\CircleMemberRole;
use App\Models\Circle;
use App\Models\User;
use App\Repositories\Interfaces\CircleRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class CircleService
{
    public function __construct(
        protected CircleRepositoryInterface $circleRepository
    ) {}

    public function createCircle(User $user, array $data): Circle
    {
        $data['owner_id'] = $user->id;
        $data['slug'] = Str::slug($data['name']) . '-' . Str::random(5);
        
        $circle = $this->circleRepository->create($data);
        
        // Owner automatically becomes a member/owner
        $this->circleRepository->addMember($circle, $user, CircleMemberRole::Owner);
        
        return $circle;
    }

    public function getCircle(string $id): ?Circle
    {
        return $this->circleRepository->findById($id);
    }

    public function joinCircle(User $user, Circle $circle): void
    {
        $this->circleRepository->addMember($circle, $user);
    }

    public function leaveCircle(User $user, Circle $circle): void
    {
        $this->circleRepository->removeMember($circle, $user);
    }

    public function getDiscoveryCircles(int $perPage = 15): LengthAwarePaginator
    {
        return $this->circleRepository->getAll($perPage);
    }

    public function getUserCircles(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return $this->circleRepository->getJoinedByUser($user, $perPage);
    }
}
