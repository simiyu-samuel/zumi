<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\Wave;
use App\Enums\WaveVisibility;
use App\Enums\WaveStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class GiftWaveTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Ensure some initial state if needed
    }

    /**
     * Test user can gift drops to another user's wave.
     */
    public function test_user_can_gift_drops_to_wave()
    {
        $sender = User::factory()->create(['drops_balance' => 1000]);
        $creator = User::factory()->create(['drops_balance' => 0]);
        $wave = Wave::factory()->create([
            'user_id' => $creator->id,
            'visibility' => WaveVisibility::Public,
            'status' => WaveStatus::Ready,
        ]);

        Sanctum::actingAs($sender);

        $response = $this->postJson("/api/v1/waves/{$wave->id}/gift", [
            'amount' => 100,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('balance', 900);

        $sender->refresh();
        $creator->refresh();

        $this->assertEquals(900, $sender->drops_balance);
        $this->assertEquals(85, $creator->drops_balance);

        $this->assertDatabaseHas('drops_ledger', [
            'user_id' => $sender->id,
            'amount' => 100,
            'direction' => 'debit',
            'reference_type' => Wave::class,
            'reference_id' => $wave->id,
        ]);

        $this->assertDatabaseHas('drops_ledger', [
            'user_id' => $creator->id,
            'amount' => 85,
            'direction' => 'credit',
            'reference_type' => Wave::class,
            'reference_id' => $wave->id,
        ]);
    }

    /**
     * Test gift fails with insufficient balance.
     */
    public function test_gift_fails_with_insufficient_balance()
    {
        $sender = User::factory()->create(['drops_balance' => 50]);
        $wave = Wave::factory()->create();

        Sanctum::actingAs($sender);

        $response = $this->postJson("/api/v1/waves/{$wave->id}/gift", [
            'amount' => 100,
        ]);

        $response->assertStatus(400)
            ->assertJsonPath('message', 'Insufficient Drops balance.');
    }

    /**
     * Test gift fails with invalid amount.
     */
    public function test_gift_fails_with_invalid_amount()
    {
        $sender = User::factory()->create(['drops_balance' => 1000]);
        $wave = Wave::factory()->create();

        Sanctum::actingAs($sender);

        // Negative amount
        $response = $this->postJson("/api/v1/waves/{$wave->id}/gift", [
            'amount' => -10,
        ]);
        $response->assertStatus(422);

        // Zero amount
        $response = $this->postJson("/api/v1/waves/{$wave->id}/gift", [
            'amount' => 0,
        ]);
        $response->assertStatus(422);

        // Non-integer amount
        $response = $this->postJson("/api/v1/waves/{$wave->id}/gift", [
            'amount' => 10.5,
        ]);
        $response->assertStatus(422);
    }

    /**
     * Test user cannot gift their own wave.
     */
    public function test_user_cannot_gift_own_wave()
    {
        $user = User::factory()->create(['drops_balance' => 1000]);
        $wave = Wave::factory()->create(['user_id' => $user->id]);

        Sanctum::actingAs($user);

        $response = $this->postJson("/api/v1/waves/{$wave->id}/gift", [
            'amount' => 100,
        ]);

        $response->assertStatus(400)
            ->assertJsonPath('message', 'You cannot transfer Drops to yourself.');
    }

    /**
     * Test unauthenticated user cannot gift.
     */
    public function test_unauthenticated_user_cannot_gift()
    {
        $wave = Wave::factory()->create();

        $response = $this->postJson("/api/v1/waves/{$wave->id}/gift", [
            'amount' => 100,
        ]);

        $response->assertStatus(401);
    }
}
