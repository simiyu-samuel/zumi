<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register()
    {
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

        $response = $this->postJson('/api/v1/auth/register', [
            'name'                  => 'John Doe',
            'username'              => 'johndoe',
            'email'                 => 'john@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['user', 'access_token']);

        $this->assertDatabaseHas('users', ['email' => 'john@example.com']);
        
        $user = User::where('email', 'john@example.com')->first();
        $this->assertTrue($user->hasRole('user'));
    }

    public function test_user_can_login()
    {
        $user = User::factory()->create([
            'email'    => 'john@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email'    => 'john@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['user', 'access_token']);
    }
}
