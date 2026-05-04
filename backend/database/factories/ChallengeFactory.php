<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Challenge>
 */
class ChallengeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'title' => $this->faker->sentence(),
            'description' => $this->faker->paragraph(),
            'type' => \App\Enums\ChallengeType::Open,
            'status' => \App\Enums\ChallengeStatus::Active,
            'prize_pool' => $this->faker->numberBetween(100, 1000),
            'ends_at' => now()->addDays(7),
        ];
    }
}
