<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name'        => fake()->unique()->company(),
            'description' => fake()->optional()->paragraph(),
            'status'      => fake()->randomElement(['active', 'inactive']),
            'budget'      => fake()->optional()->randomFloat(2, 1000, 500000),
            'user_id'     => User::factory(),
        ];
    }

    public function active(): static
    {
        return $this->state(['status' => 'active']);
    }

    public function inactive(): static
    {
        return $this->state(['status' => 'inactive']);
    }
}
