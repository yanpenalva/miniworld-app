<?php

declare(strict_types = 1);

namespace App\Http\Controllers\Api\Project;

use App\Actions\Project\{DeleteProjectAction, ListProjectAction, StoreProjectAction, UpdateProjectAction};
use App\Http\Controllers\Controller;
use App\Http\Requests\Project\{StoreProjectRequest, UpdateProjectRequest};
use App\Models\{Project, User};
use Illuminate\Http\{JsonResponse, Request};
use Illuminate\Support\Fluent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

final class ProjectController extends Controller
{
    public function index(Request $request, ListProjectAction $action): JsonResponse
    {
        $user = $request->user();

        if (!$user instanceof User) {
            throw new AccessDeniedHttpException();
        }

        $projects = $action->execute(new Fluent([
            'user_id' => $user->id,
            'search' => $request->string('search')->toString(),
            'status' => $request->string('status')->toString(),
            'order' => $request->string('order')->toString(),
            'column' => $request->string('column')->toString(),
            'paginated' => true,
            'limit' => $request->integer('limit', 15),
        ]));

        return response()->json($projects);
    }

    public function store(StoreProjectRequest $request, StoreProjectAction $action): JsonResponse
    {
        $user = $request->user();

        if (!$user instanceof User) {
            throw new AccessDeniedHttpException();
        }

        $project = $action->handle($user, $request->validated());

        return response()->json($project, 201);
    }

    public function show(Request $request, Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        return response()->json($project);
    }

    public function update(UpdateProjectRequest $request, Project $project, UpdateProjectAction $action): JsonResponse
    {
        $this->authorize('update', $project);

        return response()->json($action->handle($project, $request->validated()));
    }

    public function destroy(Request $request, Project $project, DeleteProjectAction $action): JsonResponse
    {
        $this->authorize('delete', $project);

        $action->handle($project);

        return response()->json(null, 204);
    }
}
