<?php

declare(strict_types = 1);

namespace App\Http\Controllers\Api\Project;

use App\Actions\Project\{DeleteProjectAction, ListProjectAction, StoreProjectAction, UpdateProjectAction};
use App\Http\Controllers\Controller;
use App\Http\Requests\Project\{StoreProjectRequest, UpdateProjectRequest};
use App\Http\Resources\ProjectResource;
use App\Models\{Project, User};
use Illuminate\Http\{JsonResponse, Request, Response};
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Fluent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

final class ProjectController extends Controller
{
    public function index(Request $request, ListProjectAction $action): AnonymousResourceCollection
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

        return ProjectResource::collection($projects);
    }

    public function store(StoreProjectRequest $request, StoreProjectAction $action): JsonResponse
    {
        $user = $request->user();

        if (!$user instanceof User) {
            throw new AccessDeniedHttpException();
        }

        $project = $action->handle($user, $request->validated());

        return (new ProjectResource($project->load('tasks')))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, Project $project): ProjectResource
    {
        $this->authorize('view', $project);

        return new ProjectResource($project->load('tasks'));
    }

    public function update(UpdateProjectRequest $request, Project $project, UpdateProjectAction $action): ProjectResource
    {
        $this->authorize('update', $project);

        $updated = $action->handle($project, $request->validated());

        return new ProjectResource($updated->load('tasks'));
    }

    public function destroy(Request $request, Project $project, DeleteProjectAction $action): Response
    {
        $this->authorize('delete', $project);

        $action->handle($project);

        return response()->noContent();
    }
}
