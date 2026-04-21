<?php

declare(strict_types = 1);

namespace Database\Factories;

use App\Enums\ProjectStatus;
use App\Models\User;

use function fake;

use Illuminate\Database\Eloquent\Factories\Factory;

final class ProjectFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->company(),
            'description' => fake()->optional()->paragraph(),
            'status' => fake()->randomElement(ProjectStatus::cases()),
            'budget' => fake()->optional()->randomFloat(2, 1000, 500000),
            'user_id' => User::factory(),
        ];
    }

    public function active(): static
    {
        return $this->state(['status' => ProjectStatus::ACTIVE]);
    }

    public function inactive(): static
    {
        return $this->state(['status' => ProjectStatus::INACTIVE]);
    }
}
