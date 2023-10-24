<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Website;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Project>
 */
class ProjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'client_id' => Website::factory()->create(),
            'website_id' => Website::factory()->create(),
            'user_id' => User::factory()->create(),
            'name' => $this->faker->title,
            'description' => $this->faker->sentence(),
            'active' => 1,
        ];
    }
}
