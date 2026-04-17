<?php

declare(strict_types=1);

namespace Tests\Unit\Model;

use App\Enums\ProjectStatus;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

describe('ProjectModel Test', function () {
    it('has correct fillable attributes', function () {
        $fillable = ['name', 'description', 'status', 'budget', 'user_id'];
        $project = new Project();

        expect($project->getFillable())->toEqual($fillable);
    });

    it('has correct casts', function () {
        $casts = [
            'status' => ProjectStatus::class,
            'budget' => 'decimal:2',
            'deleted_at' => 'datetime',
            'id' => 'int',
        ];
        $project = new Project();

        expect($project->getCasts())->toEqual($casts);
    });

    it('has correct table name', function () {
        $project = new Project();

        expect($project->getTable())->toBe('projects');
    });

    it('has correct primary key', function () {
        $project = new Project();

        expect($project->getKeyName())->toBe('id');
    });

    it('has correct timestamps', function () {
        $project = new Project();

        expect($project->usesTimestamps())->toBeTrue();
    });

    it('uses soft deletes', function () {
        $project = new Project();

        expect(array_key_exists('deleted_at', $project->getCasts()))->toBeTrue();
    });

    it('has a user relationship', function () {
        $project = new Project();
        $relation = $project->user();

        expect($relation)->toBeInstanceOf(BelongsTo::class)
            ->and($relation->getRelated())->toBeInstanceOf(User::class)
            ->and($relation->getForeignKeyName())->toBe('user_id');
    });

    it('has a tasks relationship', function () {
        $project = new Project();
        $relation = $project->tasks();

        expect($relation)->toBeInstanceOf(HasMany::class)
            ->and($relation->getRelated())->toBeInstanceOf(Task::class)
            ->and($relation->getForeignKeyName())->toBe('project_id');
    });
})->group('model', 'project-model');
