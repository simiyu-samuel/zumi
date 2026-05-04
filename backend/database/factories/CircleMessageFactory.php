<?php

namespace Database\Factories;

use App\Models\Circle;
use App\Models\CircleMessage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CircleMessage>
 */
class CircleMessageFactory extends Factory
{
    protected $model = CircleMessage::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'circle_id' => Circle::factory(),
            'user_id'   => User::factory(),
            'content'   => $this->faker->paragraph(),
            'metadata'  => null,
        ];
    }
}
