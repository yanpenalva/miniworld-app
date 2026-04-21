<?php

declare(strict_types = 1);

namespace Tests\Feature\Api\Project;

use App\Enums\{ProjectStatus, TaskStatus};
use App\Models\{Project, Task, User};
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->otherUser = User::factory()->create();

    Sanctum::actingAs($this->user);
});

describe('ProjectCrud', function () {
    describe('index', function () {
        it('returns only authenticated user projects', function () {
            Project::factory()->count(3)->create(['user_id' => $this->user->id]);
            Project::factory()->count(2)->create(['user_id' => $this->otherUser->id]);

            $this->getJson('/api/v1/projects?paginated=true&limit=15')
                ->assertOk()
                ->assertJsonCount(3, 'data');
        });

        it('filters projects by status', function () {
            Project::factory()->count(2)->create([
                'user_id' => $this->user->id,
                'status' => ProjectStatus::ACTIVE,
            ]);

            Project::factory()->count(1)->create([
                'user_id' => $this->user->id,
                'status' => ProjectStatus::INACTIVE,
            ]);

            $this->getJson('/api/v1/projects?status=active&paginated=true&limit=15')
                ->assertOk()
                ->assertJsonCount(2, 'data');
        });

        it('searches projects by name', function () {
            Project::factory()->create([
                'user_id' => $this->user->id,
                'name' => 'Alpha Project',
            ]);

            Project::factory()->create([
                'user_id' => $this->user->id,
                'name' => 'Beta Project',
            ]);

            $this->getJson('/api/v1/projects?search=Alpha&paginated=true&limit=15')
                ->assertOk()
                ->assertJsonCount(1, 'data');
        });

        it('orders projects by name asc', function () {
            Project::factory()->create([
                'user_id' => $this->user->id,
                'name' => 'Zulu Project',
            ]);

            Project::factory()->create([
                'user_id' => $this->user->id,
                'name' => 'Alpha Project',
            ]);

            $this->getJson('/api/v1/projects?column=name&order=asc&paginated=true&limit=15')
                ->assertOk()
                ->assertJsonPath('data.0.name', 'Alpha Project');
        });

        it('requires authentication', function () {
            Sanctum::actingAs($this->user);
            $this->app->get('auth')->forgetGuards();

            $this->getJson('/api/v1/projects?paginated=true&limit=15')
                ->assertUnauthorized();
        });
    });

    describe('store', function () {
        it('creates a project with valid data', function () {
            $payload = [
                'name' => 'New Project',
                'description' => 'Some description',
                'status' => ProjectStatus::ACTIVE->value,
                'budget' => 10000.00,
            ];

            $this->postJson('/api/v1/projects', $payload)
                ->assertCreated()
                ->assertJsonFragment(['name' => 'New Project']);

            $this->assertDatabaseHas('projects', [
                'name' => 'New Project',
                'user_id' => $this->user->id,
            ]);
        });

        it('creates a project without optional fields', function () {
            $this->postJson('/api/v1/projects', [
                'name' => 'Minimal Project',
                'status' => ProjectStatus::INACTIVE->value,
            ])
                ->assertCreated()
                ->assertJsonFragment(['name' => 'Minimal Project']);

            $this->assertDatabaseHas('projects', [
                'name' => 'Minimal Project',
                'user_id' => $this->user->id,
            ]);
        });

        it('rejects duplicate project name', function () {
            Project::factory()->create([
                'user_id' => $this->user->id,
                'name' => 'Existing Project',
            ]);

            $this->postJson('/api/v1/projects', [
                'name' => 'Existing Project',
                'status' => ProjectStatus::ACTIVE->value,
            ])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['name']);
        });

        it('rejects missing required fields', function () {
            $this->postJson('/api/v1/projects', [])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['name', 'status']);
        });

        it('rejects invalid status value', function () {
            $this->postJson('/api/v1/projects', [
                'name' => 'Test Project',
                'status' => 'invalid_status',
            ])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['status']);
        });

        it('rejects negative budget', function () {
            $this->postJson('/api/v1/projects', [
                'name' => 'Test Project',
                'status' => ProjectStatus::ACTIVE->value,
                'budget' => -100,
            ])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['budget']);
        });

        it('rejects description longer than allowed', function () {
            $this->postJson('/api/v1/projects', [
                'name' => 'Test Project',
                'description' => str_repeat('a', 5001),
                'status' => ProjectStatus::ACTIVE->value,
            ])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['description']);
        });
    });

    describe('show', function () {
        it('returns a project owned by the user', function () {
            $project = Project::factory()->create(['user_id' => $this->user->id]);

            $this->getJson("/api/v1/projects/{$project->id}")
                ->assertOk()
                ->assertJsonFragment(['id' => $project->id]);
        });

        it('denies access to another user project', function () {
            $project = Project::factory()->create(['user_id' => $this->otherUser->id]);

            $this->getJson("/api/v1/projects/{$project->id}")
                ->assertForbidden();
        });
    });

    describe('update', function () {
        it('updates a project with valid data', function () {
            $project = Project::factory()->create(['user_id' => $this->user->id]);

            $this->putJson("/api/v1/projects/{$project->id}", [
                'name' => 'Updated Name',
                'status' => ProjectStatus::INACTIVE->value,
            ])
                ->assertOk()
                ->assertJsonFragment(['name' => 'Updated Name'])
                ->assertJsonFragment(['status' => ProjectStatus::INACTIVE->value]);
        });

        it('updates a project partially without name', function () {
            $project = Project::factory()->create([
                'user_id' => $this->user->id,
                'name' => 'Original Name',
            ]);

            $this->putJson("/api/v1/projects/{$project->id}", [
                'status' => ProjectStatus::INACTIVE->value,
            ])
                ->assertOk()
                ->assertJsonFragment(['status' => ProjectStatus::INACTIVE->value]);
        });

        it('rejects duplicate name on update', function () {
            Project::factory()->create([
                'user_id' => $this->user->id,
                'name' => 'Other Project',
            ]);

            $project = Project::factory()->create(['user_id' => $this->user->id]);

            $this->putJson("/api/v1/projects/{$project->id}", [
                'name' => 'Other Project',
            ])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['name']);
        });

        it('allows updating with same name', function () {
            $project = Project::factory()->create([
                'user_id' => $this->user->id,
                'name' => 'Same Name',
            ]);

            $this->putJson("/api/v1/projects/{$project->id}", [
                'name' => 'Same Name',
            ])
                ->assertOk();
        });

        it('rejects negative budget on update', function () {
            $project = Project::factory()->create(['user_id' => $this->user->id]);

            $this->putJson("/api/v1/projects/{$project->id}", [
                'budget' => -1,
            ])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['budget']);
        });

        it('rejects description longer than allowed on update', function () {
            $project = Project::factory()->create(['user_id' => $this->user->id]);

            $this->putJson("/api/v1/projects/{$project->id}", [
                'description' => str_repeat('a', 5001),
            ])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['description']);
        });

        it('denies updating another user project', function () {
            $project = Project::factory()->create(['user_id' => $this->otherUser->id]);

            $this->putJson("/api/v1/projects/{$project->id}", [
                'name' => 'Hacked',
            ])
                ->assertForbidden();
        });
    });

    describe('destroy', function () {
        it('deletes a project without tasks', function () {
            $project = Project::factory()->create(['user_id' => $this->user->id]);

            $this->deleteJson("/api/v1/projects/{$project->id}")
                ->assertNoContent();

            $this->assertSoftDeleted('projects', ['id' => $project->id]);
        });

        it('denies deleting project with associated tasks', function () {
            $project = Project::factory()->create(['user_id' => $this->user->id]);

            Task::factory()->create([
                'user_id' => $this->user->id,
                'project_id' => $project->id,
                'status' => TaskStatus::NOT_COMPLETED,
            ]);

            $this->deleteJson("/api/v1/projects/{$project->id}")
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['project']);
        });

        it('denies deleting another user project', function () {
            $project = Project::factory()->create(['user_id' => $this->otherUser->id]);

            $this->deleteJson("/api/v1/projects/{$project->id}")
                ->assertForbidden();
        });
    });
})->group('projects');
