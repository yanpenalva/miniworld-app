<?php

declare(strict_types=1);

namespace App\Actions\Task;

use App\Models\Task;
use App\Models\User;

final readonly class StoreTaskAction
{
    /**
     * @param array<string, mixed> $data
     */
    public function handle(User $user, array $data): Task
    {
        $task = new Task();
        $task->fill($data);
        $task->user()->associate($user);
        $task->save();

        return $task->refresh();
    }
}
