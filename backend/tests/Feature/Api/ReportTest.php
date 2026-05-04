<?php

namespace Tests\Feature\Api;

use App\Enums\ReportReason;
use App\Models\User;
use App\Models\Wave;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_report_a_wave()
    {
        $reporter = User::factory()->create();
        $wave = Wave::factory()->create();

        Sanctum::actingAs($reporter);

        $response = $this->postJson('/api/v1/reports', [
            'reported_type' => 'wave',
            'reported_id'   => $wave->id,
            'reason'        => ReportReason::Spam->value,
            'description'   => 'This wave is spamming ads.',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('message', 'Report submitted successfully.')
            ->assertJsonPath('data.reason', ReportReason::Spam->value);

        $this->assertDatabaseHas('reports', [
            'reporter_id'   => $reporter->id,
            'reported_type' => Wave::class,
            'reported_id'   => $wave->id,
            'reason'        => ReportReason::Spam->value,
        ]);
    }

    public function test_user_can_report_another_user()
    {
        $reporter = User::factory()->create();
        $target = User::factory()->create();

        Sanctum::actingAs($reporter);

        $response = $this->postJson('/api/v1/reports', [
            'reported_type' => 'user',
            'reported_id'   => $target->id,
            'reason'        => ReportReason::Harassment->value,
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('reports', [
            'reported_type' => User::class,
            'reported_id'   => $target->id,
        ]);
    }
}
