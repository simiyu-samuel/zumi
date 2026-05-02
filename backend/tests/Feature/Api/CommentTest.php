<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\Wave;
use App\Models\Comment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CommentTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_comment_on_a_wave()
    {
        $user = User::factory()->create();
        $wave = Wave::factory()->create();
        
        Sanctum::actingAs($user);

        $response = $this->postJson("/api/v1/waves/{$wave->id}/comments", [
            'content' => 'Nice wave!',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('content', 'Nice wave!');

        $this->assertDatabaseHas('comments', [
            'user_id' => $user->id,
            'commentable_id' => $wave->id,
            'content' => 'Nice wave!',
        ]);
    }

    public function test_user_can_view_comments_for_a_wave()
    {
        $wave = Wave::factory()->create();
        Comment::create([
            'user_id' => User::factory()->create()->id,
            'commentable_id' => $wave->id,
            'commentable_type' => Wave::class,
            'content' => 'First comment',
        ]);

        $response = $this->getJson("/api/v1/waves/{$wave->id}/comments");

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_user_can_reply_to_a_comment()
    {
        $user = User::factory()->create();
        $wave = Wave::factory()->create();
        $parentComment = Comment::create([
            'user_id' => User::factory()->create()->id,
            'commentable_id' => $wave->id,
            'commentable_type' => Wave::class,
            'content' => 'Parent comment',
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson("/api/v1/waves/{$wave->id}/comments", [
            'content'   => 'Reply comment',
            'parent_id' => $parentComment->id,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('parent_id', $parentComment->id);

        $this->assertDatabaseHas('comments', [
            'user_id'   => $user->id,
            'parent_id' => $parentComment->id,
            'content'   => 'Reply comment',
        ]);
    }

    public function test_user_can_delete_own_comment()
    {
        $user = User::factory()->create();
        $comment = Comment::create([
            'user_id' => $user->id,
            'commentable_id' => Wave::factory()->create()->id,
            'commentable_type' => Wave::class,
            'content' => 'My comment',
        ]);

        Sanctum::actingAs($user);

        $response = $this->deleteJson("/api/v1/comments/{$comment->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('comments', ['id' => $comment->id]);
    }
}
