<?php

declare(strict_types=1);

namespace App\Actions\Task;

use App\Models\Task;
use Illuminate\Validation\ValidationException;

final readonly class DeleteTaskAction
{
    public function handle(Task $task): void
    {
        if ($task->successors()->exists()) {
            throw ValidationException::withMessages([
                'task' => 'Não é possível excluir uma tarefa que é predecessora de outra.',
            ]);
        }

        $task->delete();
    }
}
