<?php

namespace Database\Factories;

use App\Models\Circle;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Circle>
 */
class CircleFactory extends Factory
{
    protected $model = Circle::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->company;
        return [
            'owner_id'    => User::factory(),
            'name'        => $name,
            'slug'        => Str::slug($name) . '-' . Str::random(5),
            'description' => $this->faker->paragraph,
            'type'        => \App\Enums\CircleType::Public,
            'status'      => \App\Enums\CircleStatus::Active,
            'members_count' => 0,
        ];
    }
}
