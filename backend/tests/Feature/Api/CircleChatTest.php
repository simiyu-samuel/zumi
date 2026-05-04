<?php

namespace Tests\Feature\Api;

use App\Enums\CircleType;
use App\Models\Circle;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CircleChatTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_can_send_message_to_circle()
    {
        Event::fake([\App\Events\ChatMessageSent::class]);

        $owner = User::factory()->create();
        $circle = Circle::factory()->create([
            'owner_id' => $owner->id,
            'type'     => CircleType::Public,
        ]);

        $member = User::factory()->create();
        $circle->members()->attach($member, [
            'id'   => \Illuminate\Support\Str::uuid(),
            'role' => \App\Enums\CircleMemberRole::Member
        ]);

        Sanctum::actingAs($member);

        $response = $this->postJson("/api/v1/circles/{$circle->id}/messages", [
            'content' => 'Hello Circle!',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('message', 'Message sent');

        $this->assertDatabaseHas('circle_messages', [
            'circle_id' => $circle->id,
            'user_id'   => $member->id,
            'content'   => 'Hello Circle!',
        ]);

        Event::assertDispatched(\App\Events\ChatMessageSent::class, function ($event) use ($member, $circle) {
            return $event->message->content === 'Hello Circle!' &&
                   $event->message->user_id === $member->id &&
                   $event->message->circle_id === $circle->id;
        });
    }

    public function test_non_member_cannot_send_message_to_private_circle()
    {
        $owner = User::factory()->create();
        $circle = Circle::factory()->create([
            'owner_id' => $owner->id,
            'type'     => CircleType::Private,
        ]);

        $nonMember = User::factory()->create();

        Sanctum::actingAs($nonMember);

        $response = $this->postJson("/api/v1/circles/{$circle->id}/messages", [
            'content' => 'I am not a member!',
        ]);

        $response->assertStatus(403);
    }

    public function test_user_can_fetch_circle_messages()
    {
        $owner = User::factory()->create();
        $circle = Circle::factory()->create([
            'owner_id' => $owner->id,
            'type'     => CircleType::Public,
        ]);

        $member = User::factory()->create();
        $circle->members()->attach($member, [
            'id'   => \Illuminate\Support\Str::uuid(),
            'role' => \App\Enums\CircleMemberRole::Member
        ]);

        \App\Models\CircleMessage::factory()->count(10)->create([
            'circle_id' => $circle->id,
            'user_id'   => $member->id,
        ]);

        Sanctum::actingAs($member);

        $response = $this->getJson("/api/v1/circles/{$circle->id}/messages");

        $response->assertStatus(200)
            ->assertJsonCount(10, 'data');
    }
}
