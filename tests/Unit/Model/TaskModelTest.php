<?php

declare(strict_types=1);

namespace Tests\Unit\Model;

use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

describe('TaskModel Test', function () {
    it('has correct fillable attributes', function () {
        $fillable = [
            'description',
            'project_id',
            'predecessor_task_id',
            'status',
            'start_date',
            'end_date',
            'user_id',
        ];

        $task = new Task();

        expect($task->getFillable())->toEqual($fillable);
    });

    it('has correct casts', function () {
        $casts = [
            'status' => TaskStatus::class,
            'start_date' => 'date',
            'end_date' => 'date',
            'deleted_at' => 'datetime',
            'id' => 'int',
        ];

        $task = new Task();

        expect($task->getCasts())->toEqual($casts);
    });

    it('has correct table name', function () {
        $task = new Task();

        expect($task->getTable())->toBe('tasks');
    });

    it('has correct primary key', function () {
        $task = new Task();

        expect($task->getKeyName())->toBe('id');
    });

    it('has correct timestamps', function () {
        $task = new Task();

        expect($task->usesTimestamps())->toBeTrue();
    });

    it('uses soft deletes', function () {
        $task = new Task();

        expect(array_key_exists('deleted_at', $task->getCasts()))->toBeTrue();
    });

    it('has a project relationship', function () {
        $task = new Task();
        $relation = $task->project();

        expect($relation)->toBeInstanceOf(BelongsTo::class)
            ->and($relation->getRelated())->toBeInstanceOf(Project::class)
            ->and($relation->getForeignKeyName())->toBe('project_id');
    });

    it('has a user relationship', function () {
        $task = new Task();
        $relation = $task->user();

        expect($relation)->toBeInstanceOf(BelongsTo::class)
            ->and($relation->getRelated())->toBeInstanceOf(User::class)
            ->and($relation->getForeignKeyName())->toBe('user_id');
    });

    it('has a predecessor relationship', function () {
        $task = new Task();
        $relation = $task->predecessor();

        expect($relation)->toBeInstanceOf(BelongsTo::class)
            ->and($relation->getRelated())->toBeInstanceOf(Task::class)
            ->and($relation->getForeignKeyName())->toBe('predecessor_task_id');
    });

    it('has a successors relationship', function () {
        $task = new Task();
        $relation = $task->successors();

        expect($relation)->toBeInstanceOf(HasMany::class)
            ->and($relation->getRelated())->toBeInstanceOf(Task::class)
            ->and($relation->getForeignKeyName())->toBe('predecessor_task_id');
    });
})->group('model', 'task-model');
