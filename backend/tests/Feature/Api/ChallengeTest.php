<?php

namespace Tests\Feature\Api;

use App\Enums\UserRole;
use App\Models\Challenge;
use App\Models\User;
use App\Models\Wave;
use App\Models\DropsLedger;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ChallengeTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_challenge_with_escrow()
    {
        $user = User::factory()->create([
            'drops_balance' => 10000,
            'role' => UserRole::Pro
        ]);
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/challenges', [
            'title'       => 'Best Surf Move',
            'description' => 'Show me your best 360',
            'type'        => 'open',
            'prize_pool'  => 1000,
            'ends_at'     => now()->addDays(7)->toDateTimeString(),
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('challenge.prize_pool', 1000);

        $user->refresh();
        $this->assertEquals(9000, $user->drops_balance);

        $this->assertDatabaseHas('drops_ledger', [
            'user_id' => $user->id,
            'amount'  => 1000,
            'type'    => 'escrow',
            'reference_type' => Challenge::class,
        ]);
        
        $this->assertDatabaseHas('drops_ledger', [
            'user_id' => null,
            'amount'  => 1000,
            'type'    => 'escrow',
            'direction' => 'credit',
        ]);
    }

    public function test_user_can_join_challenge()
    {
        $creator = User::factory()->create(['role' => UserRole::Pro]);
        $participant = User::factory()->create();
        $challenge = Challenge::factory()->create(['user_id' => $creator->id]);
        $wave = Wave::factory()->create(['user_id' => $participant->id]);

        Sanctum::actingAs($participant);

        $response = $this->postJson("/api/v1/challenges/{$challenge->id}/join", [
            'wave_id' => $wave->id,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('challenge_participations', [
            'challenge_id' => $challenge->id,
            'user_id' => $participant->id,
            'wave_id' => $wave->id,
        ]);
    }

    public function test_user_can_vote_for_participation()
    {
        $creator = User::factory()->create(['role' => UserRole::Pro]);
        $participant = User::factory()->create();
        $voter = User::factory()->create();
        
        $challenge = Challenge::factory()->create(['user_id' => $creator->id]);
        $wave = Wave::factory()->create(['user_id' => $participant->id]);
        
        $participation = $challenge->participations()->create([
            'user_id' => $participant->id,
            'wave_id' => $wave->id,
        ]);

        Sanctum::actingAs($voter);

        $response = $this->postJson("/api/v1/challenges/participations/{$participation->id}/vote");

        $response->assertStatus(200);
        $participation->refresh();
        $this->assertEquals(1, $participation->votes_count);
    }

    public function test_creator_can_close_challenge_and_release_prize()
    {
        $creator = User::factory()->create(['role' => UserRole::Pro]);
        $participant = User::factory()->create(['drops_balance' => 0]);
        
        $challenge = Challenge::factory()->create([
            'user_id' => $creator->id,
            'prize_pool' => 1000,
            'status' => 'active'
        ]);

        // Manually setup escrow for test
        DropsLedger::create([
            'user_id' => null,
            'amount' => 1000,
            'type' => 'escrow',
            'direction' => 'credit',
            'reference_type' => Challenge::class,
            'reference_id' => $challenge->id,
            'status' => 'completed'
        ]);

        $wave = Wave::factory()->create(['user_id' => $participant->id]);
        $participation = $challenge->participations()->create([
            'user_id' => $participant->id,
            'wave_id' => $wave->id,
            'votes_count' => 10
        ]);

        Sanctum::actingAs($creator);

        $response = $this->postJson("/api/v1/challenges/{$challenge->id}/close");

        $response->assertStatus(200);
        
        $participant->refresh();
        // 1000 - 5% platform fee (50) = 950
        $this->assertEquals(950, $participant->drops_balance);
        
        $challenge->refresh();
        $this->assertEquals('completed', $challenge->status->value);
        $this->assertEquals($participant->id, $challenge->winner_id);
    }
}
