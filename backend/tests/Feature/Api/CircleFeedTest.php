<?php

namespace Tests\Feature\Api;

use App\Enums\CircleType;
use App\Enums\WaveStatus;
use App\Models\Circle;
use App\Models\User;
use App\Models\Wave;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CircleFeedTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_public_circle_feed()
    {
        $owner = User::factory()->create();
        $circle = Circle::factory()->create([
            'owner_id' => $owner->id,
            'type'     => CircleType::Public,
        ]);

        Wave::factory()->count(5)->create([
            'user_id'   => $owner->id,
            'circle_id' => $circle->id,
            'status'    => WaveStatus::Ready,
        ]);

        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson("/api/v1/circles/{$circle->id}/feed");

        $response->assertStatus(200)
            ->assertJsonCount(5, 'data');
    }

    public function test_non_member_cannot_view_private_circle_feed()
    {
        $owner = User::factory()->create();
        $circle = Circle::factory()->create([
            'owner_id' => $owner->id,
            'type'     => CircleType::Private,
        ]);

        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson("/api/v1/circles/{$circle->id}/feed");

        $response->assertStatus(403);
    }

    public function test_member_can_view_private_circle_feed()
    {
        $owner = User::factory()->create();
        $circle = Circle::factory()->create([
            'owner_id' => $owner->id,
            'type'     => CircleType::Private,
        ]);

        $member = User::factory()->create();
        $circle->members()->attach($member, [
            'id'   => \Illuminate\Support\Str::uuid(),
            'role' => \App\Enums\CircleMemberRole::Member
        ]);

        Wave::factory()->count(3)->create([
            'user_id'   => $owner->id,
            'circle_id' => $circle->id,
            'status'    => WaveStatus::Ready,
        ]);

        Sanctum::actingAs($member);

        $response = $this->getJson("/api/v1/circles/{$circle->id}/feed");

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }
}
