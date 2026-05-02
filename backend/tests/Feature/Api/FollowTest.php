<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class FollowTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_follow_and_unfollow_another_user()
    {
        $follower = User::factory()->create(['username' => 'follower']);
        $following = User::factory()->create(['username' => 'following']);
        
        Sanctum::actingAs($follower);

        // Follow
        $response = $this->postJson("/api/v1/users/{$following->id}/follow");
        $response->assertStatus(200)
            ->assertJsonPath('message', 'You are now following following.');

        $this->assertDatabaseHas('followers', [
            'follower_id'  => $follower->id,
            'following_id' => $following->id,
        ]);

        // Unfollow
        $response = $this->postJson("/api/v1/users/{$following->id}/unfollow");
        $response->assertStatus(200)
            ->assertJsonPath('message', 'You have unfollowed following.');

        $this->assertDatabaseMissing('followers', [
            'follower_id'  => $follower->id,
            'following_id' => $following->id,
        ]);
    }

    public function test_user_can_view_followers_and_following_lists()
    {
        $user = User::factory()->create(['username' => 'user']);
        $otherUser = User::factory()->create(['username' => 'other']);
        
        $user->following()->attach($otherUser->id, ['id' => \Illuminate\Support\Str::uuid()]);

        // Followers list
        $response = $this->getJson("/api/v1/users/{$otherUser->id}/followers");
        $response->assertStatus(200)
            ->assertJsonPath('data.0.id', $user->id);

        // Following list
        $response = $this->getJson("/api/v1/users/{$user->id}/following");
        $response->assertStatus(200)
            ->assertJsonPath('data.0.id', $otherUser->id);
    }
}
