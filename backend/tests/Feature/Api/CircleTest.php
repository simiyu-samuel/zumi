<?php

namespace Tests\Feature\Api;

use App\Models\Circle;
use App\Models\User;
use App\Models\Wave;
use App\Enums\WaveStatus;
use App\Enums\WaveVisibility;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CircleTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_a_circle()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/circles', [
            'name' => 'Tech Enthusiasts',
            'description' => 'A place for tech lovers',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('circles', ['name' => 'Tech Enthusiasts']);
        $this->assertDatabaseHas('circle_members', [
            'user_id' => $user->id,
            'role'    => 'owner',
        ]);
    }

    public function test_user_can_join_a_circle()
    {
        $owner = User::factory()->create();
        $circle = Circle::factory()->create(['owner_id' => $owner->id]);
        
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson("/api/v1/circles/{$circle->id}/join");

        $response->assertStatus(200);
        $this->assertDatabaseHas('circle_members', [
            'circle_id' => $circle->id,
            'user_id'   => $user->id,
            'role'      => 'member',
        ]);
    }

    public function test_feed_includes_waves_from_joined_circles()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Circle 1: User joined
        $owner1 = User::factory()->create();
        $circle1 = Circle::factory()->create(['owner_id' => $owner1->id]);
        $user->circles()->attach($circle1->id, ['id' => \Illuminate\Support\Str::uuid(), 'role' => 'member']);

        $wave1 = Wave::create([
            'id' => \Illuminate\Support\Str::uuid(),
            'user_id' => $owner1->id,
            'circle_id' => $circle1->id,
            'title' => 'Circle Wave',
            'status' => WaveStatus::Ready,
            'visibility' => WaveVisibility::Public,
            'cloudflare_id' => 'test',
        ]);

        // Circle 2: User NOT joined
        $owner2 = User::factory()->create();
        $circle2 = Circle::factory()->create(['owner_id' => $owner2->id]);
        $wave2 = Wave::create([
            'id' => \Illuminate\Support\Str::uuid(),
            'user_id' => $owner2->id,
            'circle_id' => $circle2->id,
            'title' => 'Stranger Wave',
            'status' => WaveStatus::Ready,
            'visibility' => WaveVisibility::Public,
            'cloudflare_id' => 'test2',
        ]);

        $response = $this->getJson('/api/v1/waves/followed');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Circle Wave');
    }
}
