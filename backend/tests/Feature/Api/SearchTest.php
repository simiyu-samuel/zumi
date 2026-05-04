<?php

namespace Tests\Feature\Api;

use App\Models\Circle;
use App\Models\User;
use App\Models\Wave;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_global_search_returns_users_waves_and_circles()
    {
        $user = User::factory()->create(['name' => 'John Doe', 'username' => 'johndoe']);
        User::factory()->create(['name' => 'Jane Smith']);

        $wave = Wave::factory()->create([
            'title' => 'Laravel Search Tutorial',
            'description' => 'Learn how to use Scout.'
        ]);

        $circle = Circle::factory()->create([
            'name' => 'Laravel Enthusiasts',
            'description' => 'A group for PHP developers.'
        ]);

        Sanctum::actingAs($user);

        // Search for "Laravel"
        $response = $this->getJson('/api/v1/search?q=Laravel');

        $response->assertStatus(200)
            ->assertJsonCount(0, 'users') // John Doe doesn't match "Laravel"
            ->assertJsonCount(1, 'waves') // Wave matches
            ->assertJsonCount(1, 'circles'); // Circle matches

        // Search for "John"
        $response = $this->getJson('/api/v1/search?q=John');
        $response->assertJsonCount(1, 'users');
    }

    public function test_user_search_pagination()
    {
        User::factory()->count(20)->create(['name' => 'Developer']);
        
        Sanctum::actingAs(User::factory()->create());

        $response = $this->getJson('/api/v1/search/users?q=Developer&per_page=5');

        $response->assertStatus(200)
            ->assertJsonPath('total', 20)
            ->assertJsonCount(5, 'data');
    }

    public function test_empty_query_returns_empty_results()
    {
        Sanctum::actingAs(User::factory()->create());

        $response = $this->getJson('/api/v1/search?q=');

        $response->assertStatus(200)
            ->assertJson([
                'users'   => [],
                'waves'   => [],
                'circles' => [],
            ]);
    }
}
