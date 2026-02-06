<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectListingTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_projects_index_is_paginated()
    {
        Project::factory()->count(20)->create();

        $response = $this->actingAs($this->user)
            ->getJson('/api/projects');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'links' => ['first', 'last', 'prev', 'next'],
                'meta' => ['current_page', 'last_page', 'per_page', 'total'],
            ])
            ->assertJsonCount(9, 'data');
    }

    public function test_projects_index_can_include_deep_relationships()
    {
        $project = Project::factory()->create();
        $unit = Unit::factory()->create(['project_id' => $project->id]);

        // Status updates are auto-created by Unit booted method
        $statusUpdate = $unit->statusUpdates()->first();
        $statusUpdate->revisions()->create(['revision_number' => 0]);
        $statusUpdate->approvals()->create(['approval_code' => \App\Enums\ApprovalCode::A]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/projects?include=units.statusUpdates.revisions,units.statusUpdates.approvals');

        $response->assertStatus(200);

        // Verify nested structure
        $response->assertJsonPath('data.0.units.0.status_updates.0.revisions.0.revision_number', 0);
        $response->assertJsonPath('data.0.units.0.status_updates.0.approvals.0.approval_code', 'A');
    }

    public function test_projects_index_does_not_include_relations_by_default()
    {
        $project = Project::factory()->create();
        Unit::factory()->create(['project_id' => $project->id]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/projects');

        $response->assertStatus(200);

        $response->assertJsonMissingPath('data.0.units');
    }
}
