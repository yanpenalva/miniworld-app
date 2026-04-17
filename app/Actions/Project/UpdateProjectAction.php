<?php

declare(strict_types=1);

namespace App\Actions\Project;

use App\Models\Project;

class UpdateProjectAction
{
    public function handle(Project $project, array $data): Project
    {
        $project->update($data);

        return $project->refresh();
    }
}
