<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OnboardingTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_complete_onboarding_with_interests_and_follows()
    {
        $user = User::factory()->create(['onboarding_completed' => false]);
        $suggestedUser = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/user/onboarding', [
            'interests' => ['music', 'tech'],
            'suggested_follows' => [$suggestedUser->id],
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Onboarding completed')
            ->assertJsonPath('user.onboarding_completed', true);

        $user->refresh();
        $this->assertTrue($user->onboarding_completed);
        $this->assertEquals(['music', 'tech'], $user->interests);
        $this->assertTrue($user->following->contains($suggestedUser));
    }
}
