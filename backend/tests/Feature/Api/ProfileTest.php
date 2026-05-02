<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_get_public_profile()
    {
        $user = User::factory()->create([
            'username' => 'johndoe',
            'name'     => 'John Doe',
        ]);

        $response = $this->getJson('/api/v1/users/johndoe');

        $response->assertStatus(200)
            ->assertJsonPath('data.username', 'johndoe')
            ->assertJsonPath('data.name', 'John Doe');
    }

    public function test_user_can_update_own_profile()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->patchJson('/api/v1/users/me', [
            'name' => 'Updated Name',
            'bio'  => 'Updated Bio',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('user.name', 'Updated Name')
            ->assertJsonPath('user.bio', 'Updated Bio');

        $this->assertDatabaseHas('users', [
            'id'   => $user->id,
            'name' => 'Updated Name',
            'bio'  => 'Updated Bio',
        ]);
    }
}
