<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Project;

use App\Enums\ProjectStatus;
use App\Models\Project;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->user      = User::factory()->create();
    $this->otherUser = User::factory()->create();
    Sanctum::actingAs($this->user);
});

describe('ProjectCrud', function () {
    describe('index', function () {
        it('returns only authenticated user projects', function () {
            Project::factory()->count(3)->create(['user_id' => $this->user->id]);
            Project::factory()->count(2)->create(['user_id' => $this->otherUser->id]);

            $this->getJson('/api/v1/projects')
                ->assertOk()
                ->assertJsonCount(3, 'data');
        });

        it('filters projects by status', function () {
            Project::factory()->count(2)->create(['user_id' => $this->user->id, 'status' => ProjectStatus::ACTIVE]);
            Project::factory()->count(1)->create(['user_id' => $this->user->id, 'status' => ProjectStatus::INACTIVE]);

            $this->getJson('/api/v1/projects?status=active')
                ->assertOk()
                ->assertJsonCount(2, 'data');
        });

        it('searches projects by name', function () {
            Project::factory()->create(['user_id' => $this->user->id, 'name' => 'Alpha Project']);
            Project::factory()->create(['user_id' => $this->user->id, 'name' => 'Beta Project']);

            $this->getJson('/api/v1/projects?search=Alpha')
                ->assertOk()
                ->assertJsonCount(1, 'data');
        });

        it('requires authentication', function () {
            $this->app->get('auth')->forgetGuards();

            $this->getJson('/api/v1/projects')->assertUnauthorized();
        });
    });

    describe('store', function () {
        it('creates a project with valid data', function () {
            $payload = [
                'name'        => 'New Project',
                'description' => 'Some description',
                'status'      => ProjectStatus::ACTIVE->value,
                'budget'      => 10000.00,
            ];

            $this->postJson('/api/v1/projects', $payload)
                ->assertCreated()
                ->assertJsonFragment(['name' => 'New Project']);

            $this->assertDatabaseHas('projects', ['name' => 'New Project', 'user_id' => $this->user->id]);
        });

        it('creates a project without optional fields', function () {
            $this->postJson('/api/v1/projects', [
                'name'   => 'Minimal Project',
                'status' => ProjectStatus::INACTIVE->value,
            ])->assertCreated();
        });

        it('rejects duplicate project name', function () {
            Project::factory()->create(['user_id' => $this->user->id, 'name' => 'Existing Project']);

            $this->postJson('/api/v1/projects', [
                'name'   => 'Existing Project',
                'status' => ProjectStatus::ACTIVE->value,
            ])->assertUnprocessable()
                ->assertJsonValidationErrors(['name']);
        });

        it('rejects missing required fields', function () {
            $this->postJson('/api/v1/projects', [])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['name', 'status']);
        });

        it('rejects invalid status value', function () {
            $this->postJson('/api/v1/projects', [
                'name'   => 'Test Project',
                'status' => 'invalid_status',
            ])->assertUnprocessable()
                ->assertJsonValidationErrors(['status']);
        });

        it('rejects negative budget', function () {
            $this->postJson('/api/v1/projects', [
                'name'   => 'Test Project',
                'status' => ProjectStatus::ACTIVE->value,
                'budget' => -100,
            ])->assertUnprocessable()
                ->assertJsonValidationErrors(['budget']);
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
                'name'   => 'Updated Name',
                'status' => ProjectStatus::INACTIVE->value,
            ])->assertOk()
                ->assertJsonFragment(['name' => 'Updated Name']);
        });

        it('rejects duplicate name on update', function () {
            Project::factory()->create(['user_id' => $this->user->id, 'name' => 'Other Project']);
            $project = Project::factory()->create(['user_id' => $this->user->id]);

            $this->putJson("/api/v1/projects/{$project->id}", [
                'name' => 'Other Project',
            ])->assertUnprocessable()
                ->assertJsonValidationErrors(['name']);
        });

        it('allows updating with same name', function () {
            $project = Project::factory()->create(['user_id' => $this->user->id, 'name' => 'Same Name']);

            $this->putJson("/api/v1/projects/{$project->id}", [
                'name' => 'Same Name',
            ])->assertOk();
        });

        it('denies updating another user project', function () {
            $project = Project::factory()->create(['user_id' => $this->otherUser->id]);

            $this->putJson("/api/v1/projects/{$project->id}", [
                'name' => 'Hacked',
            ])->assertForbidden();
        });
    });

    describe('destroy', function () {
        it('deletes a project without tasks', function () {
            $project = Project::factory()->create(['user_id' => $this->user->id]);

            $this->deleteJson("/api/v1/projects/{$project->id}")
                ->assertNoContent();

            $this->assertSoftDeleted('projects', ['id' => $project->id]);
        })->skip('Aguardando implementação do model Task');

        it('denies deleting project with associated tasks', function () {
            //
        })->skip('Aguardando implementação do model Task');

        it('denies deleting another user project', function () {
            $project = Project::factory()->create(['user_id' => $this->otherUser->id]);

            $this->deleteJson("/api/v1/projects/{$project->id}")
                ->assertForbidden();
        });
    });
})->group('projects');
