<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Project;

use App\Actions\Project\DeleteProjectAction;
use App\Actions\Project\StoreProjectAction;
use App\Actions\Project\UpdateProjectAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Project\StoreProjectRequest;
use App\Http\Requests\Project\UpdateProjectRequest;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class ProjectController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user instanceof User) {
            throw new AccessDeniedHttpException();
        }

        $search = $request->string('search')->toString();
        $status = $request->string('status')->toString();

        $projects = Project::query()
            ->where('user_id', $user->id)
            ->when(
                $search !== '',
                fn ($query) => $query->where('name', 'like', '%' . $search . '%')
            )
            ->when(
                $status !== '',
                fn ($query) => $query->where('status', $status)
            )
            ->latest()
            ->paginate(15);

        return response()->json($projects);
    }

    public function store(StoreProjectRequest $request, StoreProjectAction $action): JsonResponse
    {
        $user = $request->user();

        if (! $user instanceof User) {
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
