<?php

namespace App\Repositories\Interfaces;

use App\Models\Challenge;
use App\Models\User;
use Illuminate\Contracts\Pagination\CursorPaginator;

interface ChallengeRepositoryInterface
{
    public function getActive(int $perPage = 15): CursorPaginator;
    public function create(array $data): Challenge;
    public function findById(string $id): ?Challenge;
    public function update(Challenge $challenge, array $data): Challenge;
    public function delete(Challenge $challenge): bool;
    public function addParticipation(Challenge $challenge, User $user, string $waveId): void;
    public function addVote(string $participationId, User $user): void;
}
