<?php

namespace Tests\Feature\Api;

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
        Wave::factory()->count(5)->create(['visibility' => Wave::VISIBILITY_PUBLIC]);

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
            'visibility'  => Wave::VISIBILITY_PUBLIC,
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
}
