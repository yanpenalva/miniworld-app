<?php

declare(strict_types=1);

namespace App\Actions\Task;

use App\Models\Task;

final readonly class UpdateTaskAction
{
    /**
     * @param array<string, mixed> $data
     */
    public function handle(Task $task, array $data): Task
    {
        $task->update($data);

        return $task->refresh();
    }
}
