<?php

namespace Tests\Feature\Api;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SubscriptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_plans()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/subscriptions');

        $response->assertStatus(200)
            ->assertJsonStructure(['plans', 'current_subscription']);
    }

    public function test_webhook_syncs_user_role_to_pro()
    {
        $user = User::factory()->create([
            'role' => UserRole::User,
            'stripe_id' => 'cus_test123'
        ]);

        $payload = [
            'type' => 'customer.subscription.created',
            'data' => [
                'object' => [
                    'customer' => 'cus_test123',
                    'status' => 'active',
                    'items' => [
                        'data' => [
                            [
                                'price' => [
                                    'id' => config('zumi.subscriptions.plans.pro.price_id')
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $response = $this->postJson('/api/v1/webhooks/stripe', $payload);

        $response->assertStatus(200);
        $this->assertEquals(UserRole::Pro, $user->fresh()->role);
    }

    public function test_webhook_syncs_user_role_to_studio()
    {
        $user = User::factory()->create([
            'role' => UserRole::User,
            'stripe_id' => 'cus_test123'
        ]);

        $payload = [
            'type' => 'customer.subscription.created',
            'data' => [
                'object' => [
                    'customer' => 'cus_test123',
                    'status' => 'active',
                    'items' => [
                        'data' => [
                            [
                                'price' => [
                                    'id' => config('zumi.subscriptions.plans.studio.price_id')
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $response = $this->postJson('/api/v1/webhooks/stripe', $payload);

        $response->assertStatus(200);
        $this->assertEquals(UserRole::Studio, $user->fresh()->role);
    }

    public function test_webhook_reverts_to_user_on_deletion()
    {
        $user = User::factory()->create([
            'role' => UserRole::Pro,
            'stripe_id' => 'cus_test123'
        ]);

        $payload = [
            'type' => 'customer.subscription.deleted',
            'data' => [
                'object' => [
                    'customer' => 'cus_test123',
                    'status' => 'canceled',
                    'items' => [
                        'data' => [
                            [
                                'price' => [
                                    'id' => config('zumi.subscriptions.plans.pro.price_id')
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $response = $this->postJson('/api/v1/webhooks/stripe', $payload);

        $response->assertStatus(200);
        $this->assertEquals(UserRole::User, $user->fresh()->role);
    }
}
