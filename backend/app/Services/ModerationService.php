<?php

namespace App\Services;

use App\Enums\ReportStatus;
use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class ModerationService
{
    /**
     * Create a new report.
     */
    public function createReport(User $reporter, string $reportedType, string $reportedId, string $reason, ?string $description = null): Report
    {
        $typeMap = [
            'wave'    => \App\Models\Wave::class,
            'comment' => \App\Models\Comment::class,
            'user'    => \App\Models\User::class,
        ];

        return Report::create([
            'reporter_id'   => $reporter->id,
            'reported_type' => $typeMap[$reportedType],
            'reported_id'   => $reportedId,
            'reason'        => $reason,
            'description'   => $description,
            'status'        => ReportStatus::Pending,
        ]);
    }

    /**
     * Resolve a report.
     */
    public function resolveReport(Report $report, User $moderator, string $notes, \App\Enums\ModerationAction $action = \App\Enums\ModerationAction::None): void
    {
        $report->update([
            'status'          => ReportStatus::Resolved,
            'moderator_id'    => $moderator->id,
            'moderator_notes' => $notes,
            'action_taken'    => $action,
            'resolved_at'     => now(),
        ]);

        $this->performAction($report, $action);
    }

    /**
     * Perform a moderation action based on the report.
     */
    protected function performAction(Report $report, \App\Enums\ModerationAction $action): void
    {
        $reported = $report->reported;

        if (!$reported) {
            return;
        }

        switch ($action) {
            case \App\Enums\ModerationAction::DeleteContent:
                if (method_exists($reported, 'delete')) {
                    $reported->delete();
                }
                break;

            case \App\Enums\ModerationAction::BanUser:
                $user = $reported instanceof User ? $reported : $reported->user;
                if ($user) {
                    $user->update(['status' => \App\Enums\UserStatus::Banned]);
                }
                break;

            case \App\Enums\ModerationAction::WarnUser:
                // Logic to send a warning notification
                break;
        }
    }
}
