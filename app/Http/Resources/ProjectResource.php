<?php

declare(strict_types = 1);

namespace App\Http\Resources;

use App\Enums\TaskStatus;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class ProjectResource extends JsonResource
{
    public function __construct(Project $resource)
    {
        parent::__construct($resource);
    }

    public function toArray(Request $request): array
    {
        $project = $this->resource;
        $tasks = $project->relationLoaded('tasks') ? $project->tasks : collect();
        $total = $tasks->count();
        $completed = $tasks->filter(fn ($task) => $task->status === TaskStatus::COMPLETED)->count();

        return [
            'id' => $project->id,
            'name' => $project->name,
            'description' => $project->description,
            'status' => $project->status?->value,
            'budget' => $project->budget,
            'user_id' => $project->user_id,
            'progress' => [
                'total' => $total,
                'completed' => $completed,
                'percentage' => $total > 0 ? round(($completed / $total) * 100, 2) : 0,
            ],
            'created_at' => $project->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $project->updated_at?->format('Y-m-d H:i:s'),
            'created_at_formatted' => $project->created_at?->format('d/m/Y H:i'),
            'updated_at_formatted' => $project->updated_at?->format('d/m/Y H:i'),
            'deleted_at' => $project->deleted_at?->format('Y-m-d H:i:s'),
        ];
    }
}
