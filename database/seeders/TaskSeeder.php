<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    public function run(): void
    {
        $projects = Project::all();

        if ($projects->isEmpty()) {
            return;
        }

        $projects->each(
            fn (Project $project) =>
            Task::factory()
                ->count(5)
                ->create([
                    'project_id' => $project->id,
                    'user_id'    => $project->user_id,
                ])
        );
    }
}
