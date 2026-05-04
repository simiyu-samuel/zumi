<?php

namespace Tests\Feature\Api;

use App\Enums\UserRole;
use App\Models\Circle;
use App\Models\User;
use App\Services\DropsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SubscriptionPermissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_free_user_restricted_to_one_circle()
    {
        $user = User::factory()->create(['role' => UserRole::User]);
        Sanctum::actingAs($user);

        // Create first circle
        $this->postJson('/api/v1/circles', [
            'name' => 'First Circle',
            'type' => 'public',
        ])->assertStatus(201);

        // Try second circle
        $this->postJson('/api/v1/circles', [
            'name' => 'Second Circle',
            'type' => 'public',
        ])->assertStatus(403);
    }

    public function test_pro_user_can_create_multiple_circles()
    {
        $user = User::factory()->create(['role' => UserRole::Pro]);
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/circles', ['name' => 'Circle 1', 'type' => 'public'])->assertStatus(201);
        $this->postJson('/api/v1/circles', ['name' => 'Circle 2', 'type' => 'public'])->assertStatus(201);
    }

    public function test_free_user_cannot_create_premium_content()
    {
        $user = User::factory()->create(['role' => UserRole::User]);
        Sanctum::actingAs($user);

        // Skill Drop
        $this->postJson('/api/v1/skill-drops', [
            'title' => 'Tutorial',
            'description' => 'A great tutorial.',
            'price_drops' => 100,
            'type' => 'video',
            'media_url' => 'https://example.com/video.mp4',
        ])->assertStatus(403);

        // Gated Room
        $this->postJson('/api/v1/rooms', [
            'title' => 'Live Session',
            'description' => 'Join me live.',
            'entry_fee_drops' => 200,
            'scheduled_at' => now()->addHour()->toDateTimeString(),
        ])->assertStatus(403);

        // Wave Challenge
        $this->postJson('/api/v1/challenges', [
            'title' => 'Competition',
            'description' => 'Show your skills.',
            'type' => 'open',
            'prize_pool' => 1000,
            'ends_at' => now()->addDay()->toDateTimeString(),
        ])->assertStatus(403);
    }

    public function test_platform_fees_per_role()
    {
        $dropsService = app(DropsService::class);
        
        $freeUser = User::factory()->create(['role' => UserRole::User]);
        $proUser = User::factory()->create(['role' => UserRole::Pro]);
        $studioUser = User::factory()->create(['role' => UserRole::Studio]);

        $amount = 1000;

        // Free: 15% (for Circle Subscription)
        $this->assertEquals(150, $dropsService->calculatePlatformFee($amount, \App\Enums\DropsTransactionType::CircleSubscription, $freeUser));
        
        // Pro: 10%
        $this->assertEquals(100, $dropsService->calculatePlatformFee($amount, \App\Enums\DropsTransactionType::CircleSubscription, $proUser));
        
        // Studio: 7%
        $this->assertEquals(70, $dropsService->calculatePlatformFee($amount, \App\Enums\DropsTransactionType::CircleSubscription, $studioUser));

        // Challenge Prize (Release): Fixed 5% for everyone
        $this->assertEquals(50, $dropsService->calculatePlatformFee($amount, \App\Enums\DropsTransactionType::Release, $freeUser));
        $this->assertEquals(50, $dropsService->calculatePlatformFee($amount, \App\Enums\DropsTransactionType::Release, $studioUser));
    }

    public function test_payout_threshold_enforced()
    {
        $user = User::factory()->create([
            'drops_balance' => 10000, 
            'stripe_onboarding_completed' => true,
            'stripe_connect_id' => 'acct_test123'
        ]);
        Sanctum::actingAs($user);

        // Below threshold (5000)
        $this->postJson('/api/v1/payouts/withdraw', ['amount' => 1000])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['amount']);
    }

    public function test_payout_cooldown_enforced()
    {
        $user = User::factory()->create([
            'role' => UserRole::Pro, 
            'drops_balance' => 20000, 
            'stripe_onboarding_completed' => true,
            'stripe_connect_id' => 'acct_test123'
        ]);
        Sanctum::actingAs($user);

        // Mock Stripe transfer
        $this->mock(\App\Services\StripeService::class, function ($mock) {
            $mock->shouldReceive('transferToConnectedAccount')->once();
        });

        // First payout
        $this->postJson('/api/v1/payouts/withdraw', ['amount' => 5000])->assertStatus(200);

        // Immediate second payout
        $this->postJson('/api/v1/payouts/withdraw', ['amount' => 5000])
            ->assertStatus(400)
            ->assertJsonFragment(['message' => 'Payout schedule restriction. Next withdrawal available 4 weeks from now.']);
    }
}
