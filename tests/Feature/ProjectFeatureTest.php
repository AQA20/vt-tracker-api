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

    public function test_can_create_project_with_kone_project_id()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/projects', [
            'name' => 'Feature Test Project',
            'kone_project_id' => 'KONE123',
            'client_name' => 'Feature Client',
            'location' => 'Feature Location',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'data' => [
                    'name' => 'Feature Test Project',
                    'kone_project_id' => 'KONE123',
                ],
            ]);

        $this->assertDatabaseHas('projects', [
            'name' => 'Feature Test Project',
            'kone_project_id' => 'KONE123',
        ]);
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

    public function test_can_search_projects_by_id_kone_project_id_or_name()
    {
        $user = User::factory()->create();

        // Create test projects
        $project1 = Project::factory()->create([
            'name' => 'Skyline Tower',
            'kone_project_id' => 'KP001584',
        ]);
        $project2 = Project::factory()->create([
            'name' => 'Ocean View',
            'kone_project_id' => 'KP001585',
            'client_name' => 'Ocean Corp',
            'location' => 'Miami Beach',
        ]);
        $project3 = Project::factory()->create([
            'name' => 'Golden Gate Complex',
            'kone_project_id' => 'KP001586',
            'client_name' => 'Golden Properties',
            'location' => 'San Francisco',
        ]);

        // Search by kone_project_id (partial match)
        $response = $this->actingAs($user)->getJson('/api/projects?search=KP00158');
        $response->assertStatus(200)
            ->assertJsonCount(3, 'data'); // Should match all three projects

        // Search by name (partial match)
        $response = $this->actingAs($user)->getJson('/api/projects?search=Tower');
        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJson([
                'data' => [
                    ['id' => $project1->id, 'name' => 'Skyline Tower'],
                ],
            ]);

        // Search by name (case insensitive)
        $response = $this->actingAs($user)->getJson('/api/projects?search=ocean');
        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJson([
                'data' => [
                    ['id' => $project2->id, 'name' => 'Ocean View'],
                ],
            ]);

        // Search by client_name (partial match)
        $response = $this->actingAs($user)->getJson('/api/projects?search=Corp');
        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJson([
                'data' => [
                    ['id' => $project2->id, 'client_name' => 'Ocean Corp'],
                ],
            ]);

        // Search by location (partial match)
        $response = $this->actingAs($user)->getJson('/api/projects?search=Francisco');
        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJson([
                'data' => [
                    ['id' => $project3->id, 'location' => 'San Francisco'],
                ],
            ]);

        // Search with no results
        $response = $this->actingAs($user)->getJson('/api/projects?search=nonexistent');
        $response->assertStatus(200)
            ->assertJsonCount(0, 'data');
    }
}
