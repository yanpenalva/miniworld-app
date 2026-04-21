<?php

declare(strict_types = 1);

namespace Tests\Unit\Resources;

use App\Enums\{ProjectStatus, TaskStatus};
use App\Http\Resources\ProjectResource;
use App\Models\{Project, Task};
use Illuminate\Support\Collection;

describe('ProjectResource', function () {
    it('transforms project fields correctly', function () {
        $project = new Project([
            'id' => 1,
            'name' => 'Test Project',
            'description' => 'Some description',
            'status' => ProjectStatus::ACTIVE,
            'budget' => '1500.00',
            'user_id' => 42,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        $project->setRelation('tasks', collect());

        $resource = (new ProjectResource($project))->toArray(request());

        expect($resource['id'])->toBe(1)
            ->and($resource['name'])->toBe('Test Project')
            ->and($resource['description'])->toBe('Some description')
            ->and($resource['status'])->toBe('active')
            ->and($resource['budget'])->toBe('1500.00')
            ->and($resource['user_id'])->toBe(42)
            ->and($resource['created_at'])->not->toBeNull()
            ->and($resource['updated_at'])->not->toBeNull()
            ->and($resource['deleted_at'])->toBeNull();
    });

    it('returns zero progress when project has no tasks', function () {
        $project = new Project();
        $project->setRelation('tasks', collect());

        $resource = (new ProjectResource($project))->toArray(request());

        expect($resource['progress']['total'])->toBe(0)
            ->and($resource['progress']['completed'])->toBe(0)
            ->and($resource['progress']['percentage'])->toBe(0);
    });

    it('returns zero progress when tasks relation is not loaded', function () {
        $project = new Project();

        $resource = (new ProjectResource($project))->toArray(request());

        expect($resource['progress']['total'])->toBe(0)
            ->and($resource['progress']['completed'])->toBe(0)
            ->and($resource['progress']['percentage'])->toBe(0);
    });

    it('calculates progress correctly with mixed task statuses', function () {
        $completed = new Task();
        $completed->status = TaskStatus::COMPLETED;

        $notCompleted = new Task();
        $notCompleted->status = TaskStatus::NOT_COMPLETED;

        $project = new Project();
        $project->setRelation('tasks', collect([$completed, $notCompleted]));

        $resource = (new ProjectResource($project))->toArray(request());

        expect($resource['progress']['total'])->toBe(2)
            ->and($resource['progress']['completed'])->toBe(1)
            ->and($resource['progress']['percentage'])->toBe(50.0);
    });

    it('returns 100 percent when all tasks are completed', function () {
        $tasks = collect([
            tap(new Task(), fn ($t) => $t->status = TaskStatus::COMPLETED),
            tap(new Task(), fn ($t) => $t->status = TaskStatus::COMPLETED),
            tap(new Task(), fn ($t) => $t->status = TaskStatus::COMPLETED),
        ]);

        $project = new Project();
        $project->setRelation('tasks', $tasks);

        $resource = (new ProjectResource($project))->toArray(request());

        expect($resource['progress']['percentage'])->toBe(100.0)
            ->and($resource['progress']['completed'])->toBe(3)
            ->and($resource['progress']['total'])->toBe(3);
    });

    it('returns 0 percent when no tasks are completed', function () {
        $tasks = collect([
            tap(new Task(), fn ($t) => $t->status = TaskStatus::NOT_COMPLETED),
            tap(new Task(), fn ($t) => $t->status = TaskStatus::NOT_COMPLETED),
        ]);

        $project = new Project();
        $project->setRelation('tasks', $tasks);

        $resource = (new ProjectResource($project))->toArray(request());

        expect($resource['progress']['percentage'])->toBe(0.0)
            ->and($resource['progress']['completed'])->toBe(0);
    });

    it('rounds percentage to 2 decimal places', function () {
        $tasks = new Collection([
            tap(new Task(), fn ($t) => $t->status = TaskStatus::COMPLETED),
            tap(new Task(), fn ($t) => $t->status = TaskStatus::NOT_COMPLETED),
            tap(new Task(), fn ($t) => $t->status = TaskStatus::NOT_COMPLETED),
        ]);

        $project = new Project();
        $project->setRelation('tasks', $tasks);

        $resource = (new ProjectResource($project))->toArray(request());

        expect($resource['progress']['percentage'])->toBe(33.33);
    });

    it('formats dates correctly', function () {
        $project = new Project([
            'created_at' => '2025-01-15 10:30:00',
            'updated_at' => '2025-01-16 14:00:00',
            'deleted_at' => null,
        ]);

        $project->setRelation('tasks', collect());

        $resource = (new ProjectResource($project))->toArray(request());

        expect($resource['created_at'])->toBe('2025-01-15 10:30:00')
            ->and($resource['updated_at'])->toBe('2025-01-16 14:00:00')
            ->and($resource['created_at_formatted'])->toBe('15/01/2025 10:30')
            ->and($resource['updated_at_formatted'])->toBe('16/01/2025 14:00')
            ->and($resource['deleted_at'])->toBeNull();
    });

    it('formats deleted_at when soft deleted', function () {
        $project = new Project([
            'deleted_at' => '2025-06-01 08:00:00',
        ]);

        $project->setRelation('tasks', collect());

        $resource = (new ProjectResource($project))->toArray(request());

        expect($resource['deleted_at'])->toBe('2025-06-01 08:00:00');
    });

    it('returns null status when status is not set', function () {
        $project = new Project();
        $project->setRelation('tasks', collect());

        $resource = (new ProjectResource($project))->toArray(request());

        expect($resource['status'])->toBeNull();
    });
})->group('unit', 'resources');
