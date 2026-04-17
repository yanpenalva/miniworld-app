<?php

declare(strict_types = 1);

namespace App\Http\Controllers\Api\Task;

use App\Actions\Task\{DeleteTaskAction, ListTaskAction, StoreTaskAction, UpdateTaskAction, UpdateTaskStatusAction};
use App\Enums\TaskStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Task\{StoreTaskRequest, UpdateTaskRequest, UpdateTaskStatusRequest};
use App\Http\Resources\TaskResource;
use App\Models\{Task, User};
use Illuminate\Http\{JsonResponse, Request, Response};
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Fluent;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

final class TaskController extends Controller
{
    public function index(Request $request, ListTaskAction $action): AnonymousResourceCollection
    {
        $user = $request->user();

        if (!$user instanceof User) {
            throw new AccessDeniedHttpException();
        }

        $tasks = $action->execute(new Fluent([
            'user_id' => $user->id,
            'project_id' => $request->integer('project_id'),
            'status' => $request->string('status')->toString(),
            'search' => $request->string('search')->toString(),
            'column' => $request->string('column')->toString(),
            'order' => $request->string('order')->toString(),
            'limit' => $request->integer('limit', 15),
            'paginated' => filter_var($request->input('paginated', true), FILTER_VALIDATE_BOOLEAN),
        ]));

        return TaskResource::collection($tasks);
    }

    public function store(StoreTaskRequest $request, StoreTaskAction $action): JsonResponse
    {
        $user = $request->user();

        if (!$user instanceof User) {
            throw new AccessDeniedHttpException();
        }

        $task = $action->handle($user, $request->validated());

        return (new TaskResource($task->load(['project', 'predecessor'])))
            ->response()
            ->setStatusCode(SymfonyResponse::HTTP_CREATED);
    }

    public function show(Request $request, Task $task): TaskResource
    {
        $this->authorize('view', $task);

        return new TaskResource($task->load(['project', 'predecessor']));
    }

    public function update(UpdateTaskRequest $request, Task $task, UpdateTaskAction $action): TaskResource
    {
        $this->authorize('update', $task);

        $updatedTask = $action->handle($task, $request->validated());

        return new TaskResource($updatedTask->load(['project', 'predecessor']));
    }

    public function updateStatus(UpdateTaskStatusRequest $request, Task $task, UpdateTaskStatusAction $action): TaskResource
    {
        $this->authorize('updateStatus', $task);

        $status = $request->enum('status', TaskStatus::class);

        if (!$status instanceof TaskStatus) {
            throw new AccessDeniedHttpException();
        }

        $updatedTask = $action->handle($task, $status);

        return new TaskResource($updatedTask->load(['project', 'predecessor']));
    }

    public function destroy(Request $request, Task $task, DeleteTaskAction $action): Response
    {
        $this->authorize('delete', $task);

        $action->handle($task);

        return response()->noContent();
    }
}
