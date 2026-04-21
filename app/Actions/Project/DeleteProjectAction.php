<?php

declare(strict_types=1);

namespace App\Actions\Project;

use App\Models\Project;
use Illuminate\Validation\ValidationException;

final readonly class DeleteProjectAction
{
    public function handle(Project $project): void
    {
        if ($project->tasks()->exists()) {
            throw ValidationException::withMessages([
                'project' => 'Não é possível excluir um projeto que possui tarefas associadas.',
            ]);
        }

        $project->delete();
    }
}
