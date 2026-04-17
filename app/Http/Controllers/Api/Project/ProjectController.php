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
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $projects = Project::query()
            ->where('user_id', $request->user()->id)
            ->when(
                $request->search,
                fn ($q, $search) =>
                $q->where('name', 'like', "%{$search}%")
            )
            ->when(
                $request->status,
                fn ($q, $status) =>
                $q->where('status', $status)
            )
            ->latest()
            ->paginate(15);

        return response()->json($projects);
    }

    public function store(StoreProjectRequest $request, StoreProjectAction $action): JsonResponse
    {
        $project = $action->handle($request->user(), $request->validated());

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
