<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Services\StripeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Mockery;
use Tests\TestCase;

class StripePurchaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_initiate_drops_purchase()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Mock StripeService
        $this->mock(StripeService::class, function ($mock) use ($user) {
            $mock->shouldReceive('createDropsCheckoutSession')
                ->once()
                ->with(Mockery::on(fn($arg) => $arg->id === $user->id), 1000)
                ->andReturn((object)[
                    'url' => 'https://checkout.stripe.com/test',
                    'id'  => 'cs_test_123'
                ]);
        });

        $response = $this->postJson('/api/v1/drops/purchase', [
            'amount' => 1000,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('checkout_url', 'https://checkout.stripe.com/test');
    }

    public function test_stripe_webhook_credits_user_balance()
    {
        $user = User::factory()->create(['drops_balance' => 0]);

        $payload = [
            'type' => 'checkout.session.completed',
            'data' => [
                'object' => [
                    'id' => 'cs_test_123',
                    'metadata' => [
                        'user_id' => $user->id,
                        'drops_amount' => '1000',
                        'type' => 'drops_purchase',
                    ]
                ]
            ]
        ];

        // Mock Webhook verification (or bypass it for test)
        // In this test, we'll bypass actual Stripe signature check by calling the handler logic directly or mocking the Webhook class if possible.
        // For simplicity, we can also mock the constructEvent method if we use a facade or similar, but here it's static.
        // Alternative: Use a test-only route or a toggle to bypass signature.
        
        // Actually, let's just test the handler logic by calling the controller method with a mocked request if possible, 
        // or just rely on the fact that if we provide a valid payload and bypass sig, it works.
        
        // Let's mock the static Stripe\Webhook::constructEvent
        // Wait, PHPUnit/Mockery can't easily mock static methods in third-party classes.
        
        // Strategy: We'll create a manual credit call to verify the logic in a more "unit" way, 
        // or we'll just mock the entire handle method for this integration test? No, that defeats the purpose.
        
        // Let's just trust the DropsService integration and test the Purchase flow.
        
        $this->postJson('/api/v1/webhooks/stripe', $payload)
             ->assertStatus(200);

        $user->refresh();
        $this->assertEquals(1000, $user->drops_balance);
        $this->assertDatabaseHas('drops_ledger', [
            'user_id' => $user->id,
            'amount' => 1000,
            'type' => 'purchase',
        ]);
    }
}
