<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_project()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/projects', [
            'name' => 'Feature Test Project',
            'client_name' => 'Feature Client',
            'location' => 'Feature Location',
        ]);

        $response->assertStatus(201)
            ->assertJson(['data' => ['name' => 'Feature Test Project']]);

        $this->assertDatabaseHas('projects', ['name' => 'Feature Test Project']);
    }

    public function test_can_list_projects()
    {
        $user = User::factory()->create();
        Project::factory()->count(3)->create();

        $response = $this->actingAs($user)->getJson('/api/projects');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'units_count'],
                ],
            ]);
    }

    public function test_project_has_dynamic_completion_percentage()
    {
        $user = User::factory()->create();
        $project = Project::factory()->create();

        // Unit 1: 50% progress
        \App\Models\Unit::factory()->create([
            'project_id' => $project->id,
            'progress_percent' => 50,
        ]);

        // Unit 2: 100% progress
        \App\Models\Unit::factory()->create([
            'project_id' => $project->id,
            'progress_percent' => 100,
        ]);

        // Average should be 75%

        $response = $this->actingAs($user)->getJson("/api/projects/{$project->id}");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'completion_percentage' => 75,
                ],
            ])
            ->assertJsonMissing(['units_count']);
    }
}
