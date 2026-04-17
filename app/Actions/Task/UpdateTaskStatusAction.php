<?php

declare(strict_types=1);

namespace App\Actions\Task;

use App\Enums\TaskStatus;
use App\Models\Task;

final readonly class UpdateTaskStatusAction
{
    public function handle(Task $task, TaskStatus $status): Task
    {
        $task->update(['status' => $status]);

        return $task->refresh();
    }
}
