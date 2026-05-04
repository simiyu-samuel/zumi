<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Services\StripeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Mockery;
use Tests\TestCase;

class PayoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_get_onboarding_url()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->mock(StripeService::class, function ($mock) use ($user) {
            $mock->shouldReceive('createConnectAccount')
                ->once()
                ->andReturn((object)['id' => 'acct_test_123']);
            
            $mock->shouldReceive('createAccountLink')
                ->once()
                ->with('acct_test_123')
                ->andReturn((object)['url' => 'https://connect.stripe.com/onboard']);
        });

        $response = $this->postJson('/api/v1/payouts/onboard');

        $response->assertStatus(200)
            ->assertJsonPath('onboarding_url', 'https://connect.stripe.com/onboard');
        
        $user->refresh();
        $this->assertEquals('acct_test_123', $user->stripe_connect_id);
    }

    public function test_user_cannot_withdraw_without_onboarding()
    {
        $user = User::factory()->create([
            'drops_balance' => 10000,
            'stripe_onboarding_completed' => false
        ]);
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/payouts/withdraw', [
            'amount' => 5000
        ]);

        $response->assertStatus(400)
            ->assertJson(['message' => 'Please complete Stripe onboarding before withdrawing.']);
    }

    public function test_user_cannot_withdraw_less_than_minimum()
    {
        $user = User::factory()->create([
            'drops_balance' => 10000,
            'stripe_onboarding_completed' => true
        ]);
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/payouts/withdraw', [
            'amount' => 1000
        ]);

        $response->assertStatus(422); // Validation error
    }

    public function test_user_can_withdraw_drops()
    {
        $user = User::factory()->create([
            'drops_balance' => 10000,
            'stripe_connect_id' => 'acct_test_123',
            'stripe_onboarding_completed' => true
        ]);
        Sanctum::actingAs($user);

        $this->mock(StripeService::class, function ($mock) {
            $mock->shouldReceive('transferToConnectedAccount')
                ->once()
                ->with('acct_test_123', 5000)
                ->andReturn((object)['id' => 'tr_test_123']);
        });

        $response = $this->postJson('/api/v1/payouts/withdraw', [
            'amount' => 5000
        ]);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Withdrawal successful. Funds transferred to your account.']);

        $user->refresh();
        $this->assertEquals(5000, $user->drops_balance);
        $this->assertDatabaseHas('drops_ledger', [
            'user_id' => $user->id,
            'amount' => 5000,
            'type' => 'payout',
        ]);
    }
}
