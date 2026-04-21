<?php

declare(strict_types = 1);

namespace Database\Seeders;

use App\Models\{Project, User};
use Illuminate\Database\Seeder;

final class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();

        if ($users->isEmpty()) {
            return;
        }

        $users->each(
            fn (User $user) => Project::factory()
                ->count(20)
                ->create(['user_id' => $user->id])
        );
    }
}
