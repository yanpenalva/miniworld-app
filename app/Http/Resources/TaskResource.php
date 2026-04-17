<?php

declare(strict_types = 1);

namespace App\Http\Resources;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class TaskResource extends JsonResource
{
    public function __construct(Task $resource)
    {
        parent::__construct($resource);
    }

    public function toArray(Request $request): array
    {
        $task = $this->resource;

        return [
            'id' => $task->id,
            'description' => $task->description,
            'project_id' => $task->project_id,
            'predecessor_task_id' => $task->predecessor_task_id,
            'status' => $task->status?->value ?? $task->status,
            'start_date' => $task->start_date?->format('Y-m-d'),
            'end_date' => $task->end_date?->format('Y-m-d'),
            'start_date_formatted' => $task->start_date?->format('d/m/Y'),
            'end_date_formatted' => $task->end_date?->format('d/m/Y'),
            'project' => $task->project ? [
                'id' => $task->project->id,
                'name' => $task->project->name,
            ] : null,
            'predecessor' => $task->predecessor ? [
                'id' => $task->predecessor->id,
                'description' => $task->predecessor->description,
            ] : null,
            'created_at' => $task->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $task->updated_at?->format('Y-m-d H:i:s'),
            'created_at_formatted' => $task->created_at?->format('d/m/Y H:i'),
            'updated_at_formatted' => $task->updated_at?->format('d/m/Y H:i'),
            'deleted_at' => $task->deleted_at?->format('Y-m-d H:i:s'),
        ];
    }
}
