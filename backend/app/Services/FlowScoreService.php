<?php

namespace App\Services;

use App\Enums\FlowScoreTier;
use App\Models\User;

class FlowScoreService
{
    /**
     * Award Flow Score points to a user.
     */
    public function award(User $user, string $action): void
    {
        $points = config("zumi.flow_score.points.{$action}", 0);

        if ($points <= 0) {
            return;
        }

        $user->increment('flow_score', $points);
    }

    /**
     * Get the resolved tier for a given score.
     */
    public function tier(int $score): FlowScoreTier
    {
        return FlowScoreTier::fromScore($score);
    }

    /**
     * Build the full score summary for a user.
     */
    public function summary(User $user): array
    {
        $score = $user->flow_score;
        $tier  = FlowScoreTier::fromScore($score);

        return [
            'score'          => $score,
            'tier'           => $tier->value,
            'tier_label'     => $tier->label(),
            'next_threshold' => $tier->nextThreshold(),
            'points_to_next' => $tier->nextThreshold() !== null
                ? max(0, $tier->nextThreshold() - $score)
                : null,
        ];
    }
}
