<?php

namespace Tests\Feature;

use App\Enums\NotificationType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationPreferenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_update_notification_preferences()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')->patchJson('/api/v1/users/me/notifications', [
            'settings' => [
                NotificationType::Mention->value => false,
                NotificationType::Follower->value => true,
            ],
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('notification_settings.mention', false);
        $response->assertJsonPath('notification_settings.follower', true);

        $user->refresh();
        $this->assertFalse($user->wantsNotification(NotificationType::Mention));
        $this->assertTrue($user->wantsNotification(NotificationType::Follower));
    }

    public function test_user_wants_notification_defaults_to_true()
    {
        $user = User::factory()->create(); // No settings defined

        $this->assertTrue($user->wantsNotification(NotificationType::Mention));
        $this->assertTrue($user->wantsNotification(NotificationType::Follower));
    }

    public function test_notification_channels_are_empty_when_opted_out()
    {
        $user = User::factory()->create([
            'notification_settings' => [
                NotificationType::Mention->value => false,
            ],
        ]);

        $wave = \App\Models\Wave::factory()->create(['user_id' => User::factory()->create()->id]);

        $notification = new \App\Notifications\MentionNotification($wave, "Hello @{$user->username}");

        $channels = $notification->via($user);

        $this->assertEmpty($channels);
    }

    public function test_notification_channels_include_defaults_when_opted_in()
    {
        $user = User::factory()->create([
            'notification_settings' => [
                NotificationType::Mention->value => true,
            ],
        ]);

        $wave = \App\Models\Wave::factory()->create(['user_id' => User::factory()->create()->id]);

        $notification = new \App\Notifications\MentionNotification($wave, "Hello @{$user->username}");

        $channels = $notification->via($user);

        $this->assertContains('database', $channels);
        $this->assertContains('broadcast', $channels);
    }
}
