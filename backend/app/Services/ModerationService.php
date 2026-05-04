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
    public function resolveReport(Report $report, User $moderator, string $notes): void
    {
        $report->update([
            'status'          => ReportStatus::Resolved,
            'moderator_id'    => $moderator->id,
            'moderator_notes' => $notes,
            'resolved_at'     => now(),
        ]);
    }
}
