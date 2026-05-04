<?php

namespace Tests\Feature\Api;

use App\Models\SkillDrop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SkillDropTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_skill_drop()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/skill-drops', [
            'title'       => 'Mastering Laravel UUIDs',
            'description' => 'A deep dive into UUID architecture.',
            'price_drops' => 500,
            'preview_url' => 'https://stream.cloudflare.com/preview',
            'content_url' => 'https://stream.cloudflare.com/master-class',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('skill_drop.title', 'Mastering Laravel UUIDs');

        $this->assertDatabaseHas('skill_drops', [
            'title' => 'Mastering Laravel UUIDs',
            'user_id' => $user->id,
        ]);
    }

    public function test_user_can_purchase_skill_drop()
    {
        $creator = User::factory()->create();
        $buyer = User::factory()->create(['drops_balance' => 1000]);
        
        $skillDrop = SkillDrop::create([
            'user_id'     => $creator->id,
            'title'       => 'Pro UI Design',
            'slug'        => 'pro-ui-design',
            'price_drops' => 500,
            'content_url' => 'https://gated-content.com/123',
        ]);

        Sanctum::actingAs($buyer);

        $response = $this->postJson("/api/v1/skill-drops/{$skillDrop->id}/purchase");

        $response->assertStatus(200);

        $buyer->refresh();
        $creator->refresh();

        // 500 drops spent. 15% fee = 75. Creator gets 425.
        $this->assertEquals(500, $buyer->drops_balance);
        $this->assertEquals(425, $creator->drops_balance);

        $this->assertDatabaseHas('skill_drop_purchases', [
            'user_id'       => $buyer->id,
            'skill_drop_id' => $skillDrop->id,
            'amount_paid'   => 500,
        ]);
    }

    public function test_only_purchasers_can_see_content_url()
    {
        $creator = User::factory()->create();
        $buyer = User::factory()->create(['drops_balance' => 1000]);
        $guest = User::factory()->create();
        
        $skillDrop = SkillDrop::create([
            'user_id'     => $creator->id,
            'title'       => 'Gated Content',
            'slug'        => 'gated-content',
            'price_drops' => 500,
            'content_url' => 'https://secret-link.com',
        ]);

        // 1. Guest sees null
        Sanctum::actingAs($guest);
        $response = $this->getJson("/api/v1/skill-drops/{$skillDrop->id}");
        $response->assertJsonPath('data.content_url', null);

        // 2. Buyer purchases and then sees it
        Sanctum::actingAs($buyer);
        $this->postJson("/api/v1/skill-drops/{$skillDrop->id}/purchase");
        
        $response = $this->getJson("/api/v1/skill-drops/{$skillDrop->id}");
        $response->assertJsonPath('data.content_url', 'https://secret-link.com');

        // 3. Creator sees it
        Sanctum::actingAs($creator);
        $response = $this->getJson("/api/v1/skill-drops/{$skillDrop->id}");
        $response->assertJsonPath('data.content_url', 'https://secret-link.com');
    }

    public function test_user_can_view_purchased_library()
    {
        $creator = User::factory()->create();
        $buyer = User::factory()->create(['drops_balance' => 2000]);
        
        $sd1 = SkillDrop::create(['user_id' => $creator->id, 'title' => 'Drop 1', 'slug' => 'd1', 'price_drops' => 100]);
        $sd2 = SkillDrop::create(['user_id' => $creator->id, 'title' => 'Drop 2', 'slug' => 'd2', 'price_drops' => 100]);

        Sanctum::actingAs($buyer);
        $this->postJson("/api/v1/skill-drops/{$sd1->id}/purchase");
        
        $response = $this->getJson('/api/v1/skill-drops/library');
        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $sd1->id);
    }
}
