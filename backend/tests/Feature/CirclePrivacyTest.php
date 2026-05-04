<?php

namespace Tests\Feature;

use App\Enums\CircleType;
use App\Enums\CircleJoinRequestStatus;
use App\Models\Circle;
use App\Models\CircleJoinRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class CirclePrivacyTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_join_public_circle_instantly()
    {
        $user = User::factory()->create();
        $circle = Circle::factory()->create(['type' => CircleType::Public]);

        $response = $this->actingAs($user, 'sanctum')->postJson("/api/v1/circles/{$circle->id}/join");

        $response->assertStatus(200);
        $this->assertTrue($circle->members->contains($user));
    }

    public function test_user_requesting_private_circle_creates_pending_request()
    {
        Notification::fake();

        $user = User::factory()->create();
        $circle = Circle::factory()->create(['type' => CircleType::Private]);

        $response = $this->actingAs($user, 'sanctum')->postJson("/api/v1/circles/{$circle->id}/join");

        $response->assertStatus(202);
        $this->assertFalse($circle->members->contains($user));

        $this->assertDatabaseHas('circle_join_requests', [
            'user_id' => $user->id,
            'circle_id' => $circle->id,
            'status' => CircleJoinRequestStatus::Pending->value,
        ]);

        Notification::assertSentTo(
            [$circle->owner],
            \App\Notifications\CircleJoinRequestNotification::class
        );
    }

    public function test_owner_can_approve_private_circle_request()
    {
        Notification::fake();

        $owner = User::factory()->create();
        $user = User::factory()->create();
        $circle = Circle::factory()->create(['owner_id' => $owner->id, 'type' => CircleType::Private]);

        $request = CircleJoinRequest::create([
            'user_id' => $user->id,
            'circle_id' => $circle->id,
            'status' => CircleJoinRequestStatus::Pending,
        ]);

        $response = $this->actingAs($owner, 'sanctum')->postJson("/api/v1/circles/requests/{$request->id}/approve");

        $response->assertStatus(200);
        
        $request->refresh();
        $this->assertEquals(CircleJoinRequestStatus::Approved, $request->status);
        $this->assertTrue($circle->members->contains($user));

        Notification::assertSentTo(
            [$user],
            \App\Notifications\CircleApprovedNotification::class
        );
    }

    public function test_owner_can_decline_private_circle_request()
    {
        $owner = User::factory()->create();
        $user = User::factory()->create();
        $circle = Circle::factory()->create(['owner_id' => $owner->id, 'type' => CircleType::Private]);

        $request = CircleJoinRequest::create([
            'user_id' => $user->id,
            'circle_id' => $circle->id,
            'status' => CircleJoinRequestStatus::Pending,
        ]);

        $response = $this->actingAs($owner, 'sanctum')->postJson("/api/v1/circles/requests/{$request->id}/decline");

        $response->assertStatus(200);
        
        $request->refresh();
        $this->assertEquals(CircleJoinRequestStatus::Declined, $request->status);
        $this->assertFalse($circle->members->contains($user));
    }
}
