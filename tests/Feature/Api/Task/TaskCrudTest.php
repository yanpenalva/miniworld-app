<?php

declare(strict_types = 1);

namespace Tests\Feature\Api\Task;

use App\Enums\TaskStatus;
use App\Models\{Project, Task, User};
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->otherUser = User::factory()->create();

    $this->project = Project::factory()->create([
        'user_id' => $this->user->id,
        'name' => 'Projeto Principal',
    ]);

    $this->otherProject = Project::factory()->create([
        'user_id' => $this->otherUser->id,
        'name' => 'Projeto Externo',
    ]);

    Sanctum::actingAs($this->user);
});

describe('TaskCrud', function () {
    describe('index', function () {
        it('returns only authenticated user tasks', function () {
            Task::factory()->count(3)->create([
                'user_id' => $this->user->id,
                'project_id' => $this->project->id,
            ]);

            Task::factory()->count(2)->create([
                'user_id' => $this->otherUser->id,
                'project_id' => $this->otherProject->id,
            ]);

            $this->getJson('/api/v1/tasks')
                ->assertOk()
                ->assertJsonStructure([
                    'data',
                    'links',
                    'meta',
                ])
                ->assertJsonCount(3, 'data');
        });

        it('filters tasks by project', function () {
            $anotherProject = Project::factory()->create([
                'user_id' => $this->user->id,
            ]);

            Task::factory()->count(2)->create([
                'user_id' => $this->user->id,
                'project_id' => $this->project->id,
            ]);

            Task::factory()->count(1)->create([
                'user_id' => $this->user->id,
                'project_id' => $anotherProject->id,
            ]);

            $this->getJson("/api/v1/tasks?project_id={$this->project->id}")
                ->assertOk()
                ->assertJsonCount(2, 'data');
        });

        it('filters tasks by status', function () {
            Task::factory()->count(2)->create([
                'user_id' => $this->user->id,
                'project_id' => $this->project->id,
                'status' => TaskStatus::COMPLETED,
            ]);

            Task::factory()->count(1)->create([
                'user_id' => $this->user->id,
                'project_id' => $this->project->id,
                'status' => TaskStatus::NOT_COMPLETED,
            ]);

            $this->getJson('/api/v1/tasks?status=completed')
                ->assertOk()
                ->assertJsonCount(2, 'data');
        });

        it('searches tasks by description', function () {
            Task::factory()->create([
                'user_id' => $this->user->id,
                'project_id' => $this->project->id,
                'description' => 'Implement API integration',
            ]);

            Task::factory()->create([
                'user_id' => $this->user->id,
                'project_id' => $this->project->id,
                'description' => 'Fix frontend layout',
            ]);

            $this->getJson('/api/v1/tasks?search=API')
                ->assertOk()
                ->assertJsonCount(1, 'data')
                ->assertJsonFragment([
                    'description' => 'Implement API integration',
                ]);
        });

        it('searches tasks by project name', function () {
            Task::factory()->create([
                'user_id' => $this->user->id,
                'project_id' => $this->project->id,
                'description' => 'Task linked to main project',
            ]);

            $this->getJson('/api/v1/tasks?search=Principal')
                ->assertOk()
                ->assertJsonCount(1, 'data')
                ->assertJsonFragment([
                    'description' => 'Task linked to main project',
                ]);
        });

        it('searches tasks by status using whereLike', function () {
            Task::factory()->create([
                'user_id' => $this->user->id,
                'project_id' => $this->project->id,
                'status' => TaskStatus::COMPLETED,
            ]);

            Task::factory()->create([
                'user_id' => $this->user->id,
                'project_id' => $this->project->id,
                'status' => TaskStatus::NOT_COMPLETED,
            ]);

            $this->getJson('/api/v1/tasks?search=completed')
                ->assertOk()
                ->assertJsonCount(2, 'data');
        });

        it('returns formatted dates in resource collection', function () {
            Task::factory()->create([
                'user_id' => $this->user->id,
                'project_id' => $this->project->id,
                'start_date' => '2026-04-17',
                'end_date' => '2026-04-20',
            ]);

            $this->getJson('/api/v1/tasks')
                ->assertOk()
                ->assertJsonPath('data.0.start_date', '2026-04-17')
                ->assertJsonPath('data.0.end_date', '2026-04-20')
                ->assertJsonPath('data.0.start_date_formatted', '17/04/2026')
                ->assertJsonPath('data.0.end_date_formatted', '20/04/2026');
        });

        it('requires authentication', function () {
            Sanctum::actingAs(new User());
            $this->app['auth']->forgetGuards();

            $this->getJson('/api/v1/tasks')
                ->assertUnauthorized();
        });
    });

    describe('store', function () {
        it('creates a task with valid data', function () {
            $payload = [
                'description' => 'Implement project CRUD',
                'project_id' => $this->project->id,
                'status' => TaskStatus::NOT_COMPLETED->value,
                'start_date' => '2026-04-17',
                'end_date' => '2026-04-20',
            ];

            $this->postJson('/api/v1/tasks', $payload)
                ->assertCreated()
                ->assertJsonPath('data.description', 'Implement project CRUD')
                ->assertJsonPath('data.start_date', '2026-04-17')
                ->assertJsonPath('data.end_date', '2026-04-20')
                ->assertJsonPath('data.start_date_formatted', '17/04/2026')
                ->assertJsonPath('data.end_date_formatted', '20/04/2026');

            $this->assertDatabaseHas('tasks', [
                'description' => 'Implement project CRUD',
                'project_id' => $this->project->id,
                'user_id' => $this->user->id,
            ]);
        });

        it('creates a task with predecessor', function () {
            $predecessor = Task::factory()->create([
                'user_id' => $this->user->id,
                'project_id' => $this->project->id,
            ]);

            $this->postJson('/api/v1/tasks', [
                'description' => 'Dependent task',
                'project_id' => $this->project->id,
                'predecessor_task_id' => $predecessor->id,
                'status' => TaskStatus::NOT_COMPLETED->value,
            ])
                ->assertCreated()
                ->assertJsonPath('data.predecessor.id', $predecessor->id);
        });

        it('rejects missing required fields', function () {
            $this->postJson('/api/v1/tasks', [])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['description', 'project_id', 'status']);
        });

        it('rejects invalid status value', function () {
            $this->postJson('/api/v1/tasks', [
                'description' => 'Invalid status task',
                'project_id' => $this->project->id,
                'status' => 'invalid',
            ])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['status']);
        });

        it('rejects end date before start date', function () {
            $this->postJson('/api/v1/tasks', [
                'description' => 'Task with invalid dates',
                'project_id' => $this->project->id,
                'status' => TaskStatus::NOT_COMPLETED->value,
                'start_date' => '2026-04-20',
                'end_date' => '2026-04-17',
            ])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['end_date']);
        });
    });

    describe('show', function () {
        it('returns a task owned by the user', function () {
            $task = Task::factory()->create([
                'user_id' => $this->user->id,
                'project_id' => $this->project->id,
                'start_date' => '2026-04-17',
                'end_date' => '2026-04-20',
            ]);

            $this->getJson("/api/v1/tasks/{$task->id}")
                ->assertOk()
                ->assertJsonPath('data.id', $task->id)
                ->assertJsonPath('data.start_date_formatted', '17/04/2026')
                ->assertJsonPath('data.end_date_formatted', '20/04/2026');
        });

        it('denies access to another user task', function () {
            $task = Task::factory()->create([
                'user_id' => $this->otherUser->id,
                'project_id' => $this->otherProject->id,
            ]);

            $this->getJson("/api/v1/tasks/{$task->id}")
                ->assertForbidden();
        });
    });

    describe('update', function () {
        it('updates a task with valid data', function () {
            $task = Task::factory()->create([
                'user_id' => $this->user->id,
                'project_id' => $this->project->id,
                'status' => TaskStatus::NOT_COMPLETED,
            ]);

            $this->putJson("/api/v1/tasks/{$task->id}", [
                'description' => 'Updated task description',
                'status' => TaskStatus::COMPLETED->value,
            ])
                ->assertOk()
                ->assertJsonPath('data.description', 'Updated task description')
                ->assertJsonPath('data.status', TaskStatus::COMPLETED->value);

            $this->assertDatabaseHas('tasks', [
                'id' => $task->id,
                'description' => 'Updated task description',
                'status' => TaskStatus::COMPLETED->value,
            ]);
        });

        it('denies updating another user task', function () {
            $task = Task::factory()->create([
                'user_id' => $this->otherUser->id,
                'project_id' => $this->otherProject->id,
            ]);

            $this->putJson("/api/v1/tasks/{$task->id}", [
                'description' => 'Unauthorized update',
            ])->assertForbidden();
        });
    });

    describe('updateStatus', function () {
        it('updates task status', function () {
            $task = Task::factory()->create([
                'user_id' => $this->user->id,
                'project_id' => $this->project->id,
                'status' => TaskStatus::NOT_COMPLETED,
            ]);

            $this->patchJson("/api/v1/tasks/{$task->id}/status", [
                'status' => TaskStatus::COMPLETED->value,
            ])
                ->assertOk()
                ->assertJsonPath('data.status', TaskStatus::COMPLETED->value);

            $this->assertDatabaseHas('tasks', [
                'id' => $task->id,
                'status' => TaskStatus::COMPLETED->value,
            ]);
        });

        it('denies updating status of another user task', function () {
            $task = Task::factory()->create([
                'user_id' => $this->otherUser->id,
                'project_id' => $this->otherProject->id,
            ]);

            $this->patchJson("/api/v1/tasks/{$task->id}/status", [
                'status' => TaskStatus::COMPLETED->value,
            ])->assertForbidden();
        });
    });

    describe('destroy', function () {
        it('deletes a task without successors', function () {
            $task = Task::factory()->create([
                'user_id' => $this->user->id,
                'project_id' => $this->project->id,
            ]);

            $this->deleteJson("/api/v1/tasks/{$task->id}")
                ->assertNoContent();

            $this->assertSoftDeleted('tasks', [
                'id' => $task->id,
            ]);
        });

        it('denies deleting a predecessor task', function () {
            $task = Task::factory()->create([
                'user_id' => $this->user->id,
                'project_id' => $this->project->id,
            ]);

            Task::factory()->create([
                'user_id' => $this->user->id,
                'project_id' => $this->project->id,
                'predecessor_task_id' => $task->id,
            ]);

            $this->deleteJson("/api/v1/tasks/{$task->id}")
                ->assertUnprocessable();
        });

        it('denies deleting another user task', function () {
            $task = Task::factory()->create([
                'user_id' => $this->otherUser->id,
                'project_id' => $this->otherProject->id,
            ]);

            $this->deleteJson("/api/v1/tasks/{$task->id}")
                ->assertForbidden();
        });
    });
})->group('tasks');
