<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Wave;
use App\Models\Comment;
use App\Services\MentionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class MentionTest extends TestCase
{
    use RefreshDatabase;

    public function test_mention_service_extracts_usernames()
    {
        $service = new MentionService();
        $text = "Hey @john_doe and @jane, look at this! Not an email john@doe.com.";
        
        $usernames = $service->extractUsernames($text);
        
        $this->assertCount(2, $usernames);
        $this->assertContains('john_doe', $usernames);
        $this->assertContains('jane', $usernames);
    }

    public function test_wave_creation_triggers_mention_notifications()
    {
        Notification::fake();

        $author = User::factory()->create();
        $mentionedUser1 = User::factory()->create(['username' => 'alice']);
        $mentionedUser2 = User::factory()->create(['username' => 'bob']);
        $unmentionedUser = User::factory()->create(['username' => 'charlie']);

        $response = $this->actingAs($author, 'sanctum')->postJson('/api/v1/waves', [
            'title' => "Great wave @alice",
            'description' => "Check this out @bob",
            'size_bytes' => 1024,
            'stream_id' => 'test-stream-id',
        ]);

        $response->assertStatus(201);

        Notification::assertSentTo(
            [$mentionedUser1, $mentionedUser2],
            \App\Notifications\MentionNotification::class
        );

        Notification::assertNotSentTo(
            [$unmentionedUser, $author],
            \App\Notifications\MentionNotification::class
        );
    }

    public function test_comment_creation_triggers_mention_notifications()
    {
        Notification::fake();

        $author = User::factory()->create();
        $mentionedUser = User::factory()->create(['username' => 'dave']);
        $wave = Wave::factory()->create();

        $response = $this->actingAs($author, 'sanctum')->postJson("/api/v1/waves/{$wave->id}/comments", [
            'content' => "I agree with @dave on this.",
        ]);

        $response->assertStatus(201);

        Notification::assertSentTo(
            [$mentionedUser],
            \App\Notifications\MentionNotification::class
        );
    }

    public function test_author_is_not_notified_of_self_mention()
    {
        Notification::fake();

        $author = User::factory()->create(['username' => 'eve']);

        $response = $this->actingAs($author, 'sanctum')->postJson('/api/v1/waves', [
            'title' => "This is my wave @eve",
            'size_bytes' => 1024,
            'stream_id' => 'test-stream-id',
        ]);

        $response->assertStatus(201);

        Notification::assertNotSentTo(
            [$author],
            \App\Notifications\MentionNotification::class
        );
    }
}
