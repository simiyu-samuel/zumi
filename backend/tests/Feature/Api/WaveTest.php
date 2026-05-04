<?php

namespace Tests\Feature\Api;

use App\Enums\WaveStatus;
use App\Enums\WaveVisibility;
use App\Models\User;
use App\Models\Wave;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class WaveTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_waves_feed()
    {
        Wave::factory()->count(5)->create([
            'visibility' => WaveVisibility::Public,
            'status'     => WaveStatus::Ready,
        ]);

        $response = $this->getJson('/api/v1/waves');

        $response->assertStatus(200)
            ->assertJsonCount(5, 'data');
    }

    public function test_user_can_create_wave()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/waves', [
            'title'       => 'My First Wave',
            'description' => 'Cool wave description',
            'stream_id'   => 'cf-stream-id-123',
            'visibility'  => WaveVisibility::Public->value,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('wave.title', 'My First Wave');

        $this->assertDatabaseHas('waves', [
            'title'     => 'My First Wave',
            'stream_id' => 'cf-stream-id-123',
            'user_id'   => $user->id,
        ]);
    }

    public function test_user_can_like_wave()
    {
        $user = User::factory()->create();
        $wave = Wave::factory()->create();
        
        Sanctum::actingAs($user);

        $response = $this->postJson("/api/v1/waves/{$wave->id}/like");

        $response->assertStatus(200)
            ->assertJsonPath('likes_count', 1);

        $this->assertDatabaseHas('wave_likes', [
            'user_id' => $user->id,
            'wave_id' => $wave->id,
        ]);

        // Unlike
        $this->postJson("/api/v1/waves/{$wave->id}/like");
        $this->assertDatabaseMissing('wave_likes', [
            'user_id' => $user->id,
            'wave_id' => $wave->id,
        ]);
    }

    public function test_cloudflare_webhook_updates_status()
    {
        $wave = Wave::factory()->create([
            'stream_id' => 'test-stream-id',
            'status'    => WaveStatus::Pending,
        ]);

        $this->mock(\App\Services\CloudflareStreamService::class, function ($mock) {
            $mock->shouldReceive('verifyWebhookSignature')->andReturn(true);
        });

        $response = $this->postJson('/api/v1/webhooks/cloudflare', [
            'uid'    => 'test-stream-id',
            'status' => ['state' => 'ready'],
            'thumbnail' => 'https://thumb.url',
            'duration'  => 15,
            'size'      => 1024,
        ], [
            'Webhook-Signature' => 'time=123,sig1=abc'
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('waves', [
            'id'            => $wave->id,
            'status'        => WaveStatus::Ready->value,
            'thumbnail_url' => 'https://thumb.url',
        ]);
    }

    public function test_user_can_purchase_gated_wave()
    {
        $user = User::factory()->create(['drops_balance' => 100]);
        $wave = Wave::factory()->create([
            'visibility'  => WaveVisibility::Gated,
            'gated_drops' => 50,
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson("/api/v1/waves/{$wave->id}/purchase");

        $response->assertStatus(200);
        $this->assertDatabaseHas('wave_purchases', [
            'user_id' => $user->id,
            'wave_id' => $wave->id,
            'amount_paid' => 50,
        ]);

        $user->refresh();
        $this->assertEquals(50, $user->drops_balance);
    }

    public function test_user_can_view_followed_waves_feed()
    {
        $user = User::factory()->create();
        $following = User::factory()->create();
        
        // Follow the user
        $user->following()->attach($following->id);

        Wave::factory()->count(3)->create([
            'user_id'    => $following->id,
            'visibility' => WaveVisibility::Public,
            'status'     => WaveStatus::Ready,
        ]);

        Wave::factory()->count(2)->create([
            'visibility' => WaveVisibility::Public,
            'status'     => WaveStatus::Ready,
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/waves/followed');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_user_can_record_wave_view()
    {
        $user = User::factory()->create();
        $wave = Wave::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->postJson("/api/v1/waves/{$wave->id}/view");

        $response->assertStatus(200);
        $this->assertDatabaseHas('waves', [
            'id' => $wave->id,
            'views_count' => 1,
        ]);
    }
}
