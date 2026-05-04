<?php

namespace Tests\Feature\Api;

use App\Enums\ModerationAction;
use App\Enums\ReportStatus;
use App\Enums\UserStatus;
use App\Models\Report;
use App\Models\User;
use App\Models\Wave;
use App\Services\ModerationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModerationActionTest extends TestCase
{
    use RefreshDatabase;

    protected ModerationService $moderationService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->moderationService = app(ModerationService::class);
    }

    public function test_resolve_report_with_delete_content_action()
    {
        $user = User::factory()->create();
        $wave = Wave::factory()->create(['user_id' => $user->id]);
        $moderator = User::factory()->create();

        $report = Report::create([
            'reporter_id'   => User::factory()->create()->id,
            'reported_type' => Wave::class,
            'reported_id'   => $wave->id,
            'reason'        => 'inappropriate_content',
            'status'        => ReportStatus::Pending,
        ]);

        $this->moderationService->resolveReport($report, $moderator, 'Deleting content', ModerationAction::DeleteContent);

        $this->assertSoftDeleted('waves', ['id' => $wave->id]);
        $this->assertEquals(ReportStatus::Resolved, $report->fresh()->status);
        $this->assertEquals(ModerationAction::DeleteContent, $report->fresh()->action_taken);
    }

    public function test_resolve_report_with_ban_user_action()
    {
        $user = User::factory()->create(['status' => UserStatus::Active]);
        $moderator = User::factory()->create();

        $report = Report::create([
            'reporter_id'   => User::factory()->create()->id,
            'reported_type' => User::class,
            'reported_id'   => $user->id,
            'reason'        => 'harassment',
            'status'        => ReportStatus::Pending,
        ]);

        $this->moderationService->resolveReport($report, $moderator, 'Banning user', ModerationAction::BanUser);

        $this->assertEquals(UserStatus::Banned, $user->fresh()->status);
        $this->assertEquals(ReportStatus::Resolved, $report->fresh()->status);
        $this->assertEquals(ModerationAction::BanUser, $report->fresh()->action_taken);
    }
}
