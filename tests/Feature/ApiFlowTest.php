<?php

namespace Tests\Feature;

use App\Enums\UnitCategory;
use App\Models\Project;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_full_flow()
    {
        $this->seed();
        $user = User::factory()->create();

        // 1. Create Project
        $projectResponse = $this->actingAs($user)->postJson('/api/projects', [
            'name' => 'API Test Project',
            'client_name' => 'API Client',
            'location' => 'API City',
        ]);
        $projectResponse->assertStatus(201);
        $projectId = $projectResponse->json('id');

        // 2. Create Unit (Trigger Auto-Generation via Service)
        $unitResponse = $this->actingAs($user)->postJson("/api/projects/{$projectId}/units", [
            'unit_type' => 'KONE MonoSpace 700',
            'equipment_number' => 'API-001',
            'category' => UnitCategory::ELEVATOR,
        ]);
        $unitResponse->assertStatus(201);
        $unitId = $unitResponse->json('id');

        $this->assertDatabaseHas('unit_stages', ['unit_id' => $unitId]);

        // 3. Get Stages
        $stagesResponse = $this->actingAs($user)->getJson("/api/units/{$unitId}/stages");
        $stagesResponse->assertStatus(200);
        $stage1Id = $stagesResponse->json('0.id');

        // 4. Get Tasks for Stage 1
        $tasksResponse = $this->actingAs($user)->getJson("/api/stages/{$stage1Id}/tasks");
        $tasksResponse->assertStatus(200);
        $tasks = $tasksResponse->json();

        // 5. Complete Tasks via API
        foreach ($tasks as $task) {
            $taskId = $task['id'];

            $payload = ['status' => 'pass'];

            $this->actingAs($user)->putJson("/api/tasks/{$taskId}", $payload)
                ->assertStatus(200);
        }

        // 6. Check Stage 1 Completion
        $this->actingAs($user)->getJson("/api/stages/{$stage1Id}")
            ->assertJson(['status' => 'completed']);

        // 7. Check Progress (S1 done = 5/69 = 8%)
        $this->actingAs($user)->getJson("/api/units/{$unitId}/progress")
            ->assertJsonFragment(['progress' => 8]);
    }
}
