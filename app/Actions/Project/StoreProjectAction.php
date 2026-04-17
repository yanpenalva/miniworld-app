<?php

declare(strict_types=1);

namespace App\Actions\Project;

use App\Models\Project;
use App\Models\User;

final readonly class StoreProjectAction
{
    /**
     * @param array<string, mixed> $data
     */
    public function handle(User $user, array $data): Project
    {
        $project = new Project();
        $project->fill($data);
        $project->user()->associate($user);
        $project->save();

        return $project->refresh();
    }
}
