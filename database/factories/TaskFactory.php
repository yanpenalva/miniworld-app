<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

final class TaskFactory extends Factory
{
    public function definition(): array
    {
        $startDate = fake()->optional()->date();

        return [
            'description' => fake()->sentence(),
            'project_id' => Project::factory(),
            'predecessor_task_id' => null,
            'status' => fake()->randomElement(TaskStatus::cases()),
            'start_date' => $startDate,
            'end_date' => $startDate
                ? fake()->optional()->dateTimeBetween($startDate, '+1 year')?->format('Y-m-d')
                : null,
            'user_id' => User::factory(),
        ];
    }

    public function completed(): static
    {
        return $this->state(['status' => TaskStatus::COMPLETED]);
    }

    public function notCompleted(): static
    {
        return $this->state(['status' => TaskStatus::NOT_COMPLETED]);
    }
}
