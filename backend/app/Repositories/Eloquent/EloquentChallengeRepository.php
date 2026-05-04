<?php

namespace App\Repositories\Eloquent;

use App\Enums\ChallengeStatus;
use App\Models\Challenge;
use App\Models\ChallengeParticipation;
use App\Models\ChallengeVote;
use App\Models\User;
use App\Repositories\Interfaces\ChallengeRepositoryInterface;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Support\Facades\DB;

class EloquentChallengeRepository implements ChallengeRepositoryInterface
{
    public function getActive(int $perPage = 15): CursorPaginator
    {
        return Challenge::where('status', ChallengeStatus::Active)
            ->where('ends_at', '>', now())
            ->with(Challenge::DEFAULT_EAGER_LOAD)
            ->latest()
            ->cursorPaginate($perPage);
    }

    public function create(array $data): Challenge
    {
        return Challenge::create($data);
    }

    public function findById(string $id): ?Challenge
    {
        return Challenge::with(Challenge::DETAILED_EAGER_LOAD)->find($id);
    }

    public function update(Challenge $challenge, array $data): Challenge
    {
        $challenge->update($data);
        return $challenge;
    }

    public function delete(Challenge $challenge): bool
    {
        return $challenge->delete();
    }

    public function addParticipation(Challenge $challenge, User $user, string $waveId): void
    {
        $challenge->participations()->create([
            'user_id' => $user->id,
            'wave_id' => $waveId,
        ]);
    }

    public function addVote(string $participationId, User $user): void
    {
        DB::transaction(function () use ($participationId, $user) {
            ChallengeVote::create([
                'participation_id' => $participationId,
                'user_id' => $user->id,
            ]);

            ChallengeParticipation::where('id', $participationId)->increment('votes_count');
        });
    }
}
