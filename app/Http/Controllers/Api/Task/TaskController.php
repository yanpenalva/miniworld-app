<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Task;

use App\Actions\Task\DeleteTaskAction;
use App\Actions\Task\StoreTaskAction;
use App\Actions\Task\UpdateTaskAction;
use App\Actions\Task\UpdateTaskStatusAction;
use App\Enums\TaskStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Task\StoreTaskRequest;
use App\Http\Requests\Task\UpdateTaskRequest;
use App\Http\Requests\Task\UpdateTaskStatusRequest;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class TaskController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user instanceof User) {
            throw new AccessDeniedHttpException();
        }

        $projectId = $request->integer('project_id');
        $status = $request->string('status')->toString();
        $search = $request->string('search')->toString();

        $tasks = Task::query()
            ->where('user_id', $user->id)
            ->when(
                $projectId > 0,
                fn ($query) => $query->where('project_id', $projectId)
            )
            ->when(
                $status !== '',
                fn ($query) => $query->where('status', $status)
            )
            ->when(
                $search !== '',
                fn ($query) => $query->where('description', 'like', '%' . $search . '%')
            )
            ->with(['project', 'predecessor'])
            ->latest()
            ->paginate(15);

        return response()->json($tasks);
    }

    public function store(StoreTaskRequest $request, StoreTaskAction $action): JsonResponse
    {
        $user = $request->user();

        if (! $user instanceof User) {
            throw new AccessDeniedHttpException();
        }

        $task = $action->handle($user, $request->validated());

        return response()->json($task->load(['project', 'predecessor']), 201);
    }

    public function show(Request $request, Task $task): JsonResponse
    {
        $this->authorize('view', $task);

        return response()->json($task->load(['project', 'predecessor']));
    }

    public function update(UpdateTaskRequest $request, Task $task, UpdateTaskAction $action): JsonResponse
    {
        $this->authorize('update', $task);

        return response()->json($action->handle($task, $request->validated()));
    }

    public function updateStatus(UpdateTaskStatusRequest $request, Task $task, UpdateTaskStatusAction $action): JsonResponse
    {
        $this->authorize('updateStatus', $task);

        $status = $request->enum('status', TaskStatus::class);

        if (! $status instanceof TaskStatus) {
            throw new AccessDeniedHttpException();
        }

        return response()->json($action->handle($task, $status));
    }

    public function destroy(Request $request, Task $task, DeleteTaskAction $action): JsonResponse
    {
        $this->authorize('delete', $task);

        $action->handle($task);

        return response()->json(null, 204);
    }
}
