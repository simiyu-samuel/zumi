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
use App\Repositories\Interfaces\WaveRepositoryInterface;
use App\Services\DropsService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CircleService
{
    public function __construct(
        protected CircleRepositoryInterface $circleRepository,
        protected WaveRepositoryInterface $waveRepository,
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
        // 1. Handle Private Circles (Approval Required)
        if ($circle->type === CircleType::Private) {
            $this->requestToJoin($user, $circle);
            return;
        }

        // 2. Handle Gated Circles (Paid)
        if ($circle->type === CircleType::Gated && $circle->monthly_drops_price > 0) {
            if ($user->drops_balance < $circle->monthly_drops_price) {
                throw new \DomainException('Insufficient Drops balance to join this Circle.');
            }

            DB::transaction(function () use ($user, $circle) {
                $this->dropsService->transfer(
                    $user,
                    $circle->owner,
                    $circle->monthly_drops_price,
                    DropsTransactionType::CircleSubscription,
                    $circle,
                    [
                        'title' => 'Circle subscription: ' . $circle->name,
                        'debit_description' => "Joined Circle: {$circle->name}",
                        'credit_description' => "New Member in Circle: {$circle->name}",
                    ],
                );
                $this->circleRepository->addMember($circle, $user);
                $this->flowScoreService->award($circle->owner, 'circle_joined');
            });

            return;
        }

        // 3. Handle Public Circles (Immediate Join)
        $this->circleRepository->addMember($circle, $user);
        $this->flowScoreService->award($circle->owner, 'circle_joined');
    }

    
    /**
     * Create a pending join request for a private circle.
     */
    protected function requestToJoin(User $user, Circle $circle): void
    {
        $existing = $this->circleRepository->findPendingJoinRequest($user->id, $circle->id);

        if ($existing) {
            throw new \DomainException('You already have a pending join request for this Circle.');
        }

        $request = $this->circleRepository->createJoinRequest($user->id, $circle->id);

        $circle->owner->notify(new \App\Notifications\CircleJoinRequestNotification($request));
    }

    /**
     * Approve a pending join request.
     */
    public function approveJoinRequest(\App\Models\CircleJoinRequest $request): void
    {
        if ($request->status !== \App\Enums\CircleJoinRequestStatus::Pending) {
            throw new \DomainException('This request is no longer pending.');
        }

        DB::transaction(function () use ($request) {
            $request->update(['status' => \App\Enums\CircleJoinRequestStatus::Approved]);
            $this->circleRepository->addMember($request->circle, $request->user);
            $this->flowScoreService->award($request->circle->owner, 'circle_joined');
            
            $request->user->notify(new \App\Notifications\CircleApprovedNotification($request->circle));
        });
    }

    /**
     * Decline a pending join request.
     */
    public function declineJoinRequest(\App\Models\CircleJoinRequest $request): void
    {
        $request->update(['status' => \App\Enums\CircleJoinRequestStatus::Declined]);
    }

    /**
     * Get pending join requests for a circle.
     */
    public function getPendingRequests(Circle $circle, int $perPage = 15): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return $this->circleRepository->getPendingJoinRequests($circle, $perPage);
    }

    /**
     * Get paginated feed for a circle.
     */
    public function getFeed(Circle $circle, int $perPage = 15): \Illuminate\Contracts\Pagination\CursorPaginator
    {
        return $this->waveRepository->getCircleFeed($circle->id, $perPage);
    }

    /**
     * Send a real-time message to a circle.
     */
    public function sendMessage(User $user, Circle $circle, string $content): \App\Models\CircleMessage
    {
        // 1. Ensure user is a member
        if (!$circle->members()->where('user_id', $user->id)->exists()) {
            throw new \DomainException('You must be a member of this Circle to send messages.');
        }

        // 2. Create message
        $message = \App\Models\CircleMessage::create([
            'circle_id' => $circle->id,
            'user_id'   => $user->id,
            'content'   => $content,
        ]);

        // 3. Broadcast
        broadcast(new \App\Events\ChatMessageSent($message->load('user')))->toOthers();

        return $message;
    }

    /**
     * Get paginated messages for a circle.
     */
    public function getMessages(Circle $circle, int $perPage = 50): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return \App\Models\CircleMessage::with('user')
            ->where('circle_id', $circle->id)
            ->latest()
            ->paginate($perPage);
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
        $since = now()->subDays(30);

        return [
            'total_members'   => $circle->members_count,
            'new_members_30d' => $this->circleRepository->getMembersJoinedSince($circle, $since),
            'total_revenue'   => $this->circleRepository->getRevenue($circle),
            'revenue_30d'     => $this->circleRepository->getRevenue($circle, $since),
            'top_waves'       => $this->waveRepository->getTopForCircle($circle->id)->map(fn ($w) => [
                'id'          => $w->id,
                'title'       => $w->title,
                'views_count' => $w->views_count,
                'likes_count' => $w->likes_count,
            ]),
        ];
    }
}
