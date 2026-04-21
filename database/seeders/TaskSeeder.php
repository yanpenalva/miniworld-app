<?php

declare(strict_types = 1);

namespace Database\Seeders;

use App\Models\{Project, Task};
use Illuminate\Database\Seeder;

final class TaskSeeder extends Seeder
{
    public function run(): void
    {
        $projects = Project::all();

        if ($projects->isEmpty()) {
            return;
        }

        $projects->each(
            fn (Project $project) => Task::factory()
                ->count(20)
                ->create([
                    'project_id' => $project->id,
                    'user_id' => $project->user_id,
                ])
        );
    }
}
