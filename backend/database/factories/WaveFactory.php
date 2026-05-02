<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Wave;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Wave>
 */
class WaveFactory extends Factory
{
    protected $model = Wave::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id'       => User::factory(),
            'title'         => $this->faker->sentence(),
            'description'   => $this->faker->paragraph(),
            'stream_id'     => $this->faker->uuid(),
            'thumbnail_url' => $this->faker->imageUrl(),
            'visibility'    => Wave::VISIBILITY_PUBLIC,
            'gated_drops'   => 0,
            'likes_count'   => 0,
            'comments_count'=> 0,
            'shares_count'  => 0,
            'views_count'   => 0,
        ];
    }
}
