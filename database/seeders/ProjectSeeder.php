<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();

        if ($users->isEmpty()) {
            return;
        }

        $users->each(fn (User $user) =>
            Project::factory()
                ->count(5)
                ->create(['user_id' => $user->id])
        );
    }
}
