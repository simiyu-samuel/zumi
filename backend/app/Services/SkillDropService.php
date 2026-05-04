<?php

namespace App\Services;

use App\Enums\DropsTransactionType;
use App\Models\SkillDrop;
use App\Models\User;
use App\Repositories\Interfaces\SkillDropRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SkillDropService
{
    public function __construct(
        protected SkillDropRepositoryInterface $skillDropRepository,
        protected DropsService $dropsService
    ) {}

    public function getAll(int $perPage = 15): LengthAwarePaginator
    {
        return $this->skillDropRepository->getAll($perPage);
    }

    public function getByUser(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return $this->skillDropRepository->getByUser($user, $perPage);
    }

    public function getLibrary(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return $this->skillDropRepository->getPurchasedByUser($user, $perPage);
    }

    public function create(User $user, array $data): SkillDrop
    {
        $data['user_id'] = $user->id;
        $data['slug'] = Str::slug($data['title']) . '-' . Str::random(5);
        
        return $this->skillDropRepository->create($data);
    }

    public function update(SkillDrop $skillDrop, array $data): SkillDrop
    {
        return $this->skillDropRepository->update($skillDrop, $data);
    }

    public function delete(SkillDrop $skillDrop): bool
    {
        return $this->skillDropRepository->delete($skillDrop);
    }

    public function purchase(User $user, SkillDrop $skillDrop): array
    {
        try {
            if ($user->id === $skillDrop->user_id) {
                throw new \Exception('You cannot purchase your own Skill Drop.');
            }

            if ($skillDrop->buyers()->where('user_id', $user->id)->exists()) {
                throw new \Exception('You already own this Skill Drop.');
            }

            if ($user->drops_balance < $skillDrop->price_drops) {
                throw new \Exception('Insufficient Drops balance.');
            }

            DB::transaction(function () use ($user, $skillDrop) {
                // 1. Transfer funds
                $this->dropsService->transfer(
                    $user,
                    $skillDrop->user,
                    $skillDrop->price_drops,
                    DropsTransactionType::Spend,
                    $skillDrop,
                    ['title' => 'Purchase Skill Drop: ' . $skillDrop->title]
                );

                // 2. Record purchase
                $this->skillDropRepository->recordPurchase($skillDrop, $user, $skillDrop->price_drops);
            });

            return ['success' => true];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
}
