<?php

namespace App\Services;

use App\Enums\ChallengeStatus;
use App\Models\Challenge;
use App\Models\User;
use App\Models\Wave;
use App\Repositories\Interfaces\ChallengeRepositoryInterface;
use Exception;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Support\Facades\DB;

class ChallengeService
{
    public function __construct(
        protected ChallengeRepositoryInterface $challengeRepository,
        protected DropsService $dropsService
    ) {}

    public function getActiveChallenges(int $perPage = 15): CursorPaginator
    {
        return $this->challengeRepository->getActive($perPage);
    }

    /**
     * Create a new challenge and hold prize pool in escrow.
     */
    public function createChallenge(User $user, array $data): array
    {
        try {
            return DB::transaction(function () use ($user, $data) {
                $prizePool = $data['prize_pool'] ?? 0;
                $challengeId = (string) \Illuminate\Support\Str::uuid();

                // 1. Hold funds in escrow using the generated ID
                if ($prizePool > 0) {
                    $this->dropsService->holdInEscrow(
                        $user,
                        $prizePool,
                        Challenge::class,
                        $challengeId
                    );
                }

                // 2. Create challenge with the predefined ID
                $data['id'] = $challengeId;
                $data['user_id'] = $user->id;
                $data['status'] = ChallengeStatus::Active;
                $challenge = $this->challengeRepository->create($data);

                return [
                    'success' => true,
                    'challenge' => $challenge,
                ];
            });
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Join a challenge by submitting a response wave.
     */
    public function joinChallenge(User $user, Challenge $challenge, string $waveId): array
    {
        try {
            if ($challenge->status !== ChallengeStatus::Active) {
                throw new Exception('This challenge is not active.');
            }

            if ($challenge->ends_at->isPast()) {
                throw new Exception('This challenge has ended.');
            }

            $wave = Wave::findOrFail($waveId);
            if ($wave->user_id !== $user->id) {
                throw new Exception('You can only submit your own Waves.');
            }

            $this->challengeRepository->addParticipation($challenge, $user, $waveId);

            return ['success' => true];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Vote for a participation.
     */
    public function vote(User $user, string $participationId): array
    {
        try {
            $this->challengeRepository->addVote($participationId, $user);
            return ['success' => true];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Close a challenge and release the prize pool to the winner.
     */
    public function closeChallenge(Challenge $challenge): array
    {
        try {
            return DB::transaction(function () use ($challenge) {
                if ($challenge->status !== ChallengeStatus::Active) {
                    throw new Exception('Challenge is not active.');
                }

                // 1. Determine winner (participation with most votes)
                $winnerParticipation = $challenge->participations()->orderByDesc('votes_count')->first();

                if (!$winnerParticipation) {
                    // No participants? Refund the creator.
                    if ($challenge->prize_pool > 0) {
                        $this->dropsService->refundFromEscrow(
                            $challenge->user,
                            $challenge->prize_pool,
                            Challenge::class,
                            $challenge->id
                        );
                    }
                    $challenge->update(['status' => ChallengeStatus::Cancelled]);
                    return ['success' => true, 'message' => 'No participants. Prize pool refunded.'];
                }

                $winner = $winnerParticipation->user;

                // 2. Release Escrow to Winner
                if ($challenge->prize_pool > 0) {
                    $this->dropsService->releaseFromEscrow(
                        $winner,
                        $challenge->prize_pool,
                        Challenge::class,
                        $challenge->id
                    );
                }

                // 3. Mark as completed
                $challenge->update([
                    'status' => ChallengeStatus::Completed,
                    'winner_id' => $winner->id,
                ]);

                return [
                    'success' => true,
                    'winner' => $winner,
                ];
            });
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
}
