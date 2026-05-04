<?php

namespace App\Services;

use App\Enums\CircleMemberRole;
use App\Enums\CircleType;
use App\Enums\DropsTransactionType;
use App\Models\Circle;
use App\Models\DropsLedger;
use App\Models\User;
use App\Models\Wave;
use App\Repositories\Interfaces\CircleRepositoryInterface;
use App\Services\DropsService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CircleService
{
    public function __construct(
        protected CircleRepositoryInterface $circleRepository,
        protected DropsService $dropsService,
        protected FlowScoreService $flowScoreService,
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
        $requiresDrops = in_array($circle->type, [CircleType::FreeGated, CircleType::Premium])
            && $circle->monthly_drops_price > 0;

        if ($requiresDrops) {
            if ($user->drops_balance < $circle->monthly_drops_price) {
                throw new \DomainException('Insufficient Drops balance to join this Circle.');
            }

            DB::transaction(function () use ($user, $circle) {
                $this->dropsService->transfer(
                    $user,
                    $circle->owner,
                    $circle->monthly_drops_price,
                    DropsTransactionType::Spend,
                    $circle,
                    ['title' => 'Circle subscription: ' . $circle->name],
                );
                $this->circleRepository->addMember($circle, $user);

                // Award Flow Score to circle owner
                $this->flowScoreService->award($circle->owner, 'circle_joined');
            });

            return;
        }

        $this->circleRepository->addMember($circle, $user);

        // Award Flow Score to circle owner
        $this->flowScoreService->award($circle->owner, 'circle_joined');
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

    public function getInsights(Circle $circle): array
    {
        $totalRevenue = DropsLedger::where('reference_type', Circle::class)
            ->where('reference_id', $circle->id)
            ->where('direction', 'credit') // revenue credited to owner
            ->sum('amount');

        $membersLast30Days = $circle->members()
            ->wherePivot('joined_at', '>=', now()->subDays(30))
            ->count();

        $topWaves = Wave::where('circle_id', $circle->id)
            ->orderByDesc('views_count')
            ->limit(3)
            ->get();

        return [
            'total_members'        => $circle->members_count,
            'members_last_30_days' => $membersLast30Days,
            'total_revenue_drops'  => (int) $totalRevenue,
            'top_waves'            => $topWaves->map(fn ($w) => [
                'id'          => $w->id,
                'title'       => $w->title,
                'views_count' => $w->views_count,
                'likes_count' => $w->likes_count,
            ]),
        ];
    }
}
