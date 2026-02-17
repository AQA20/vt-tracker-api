<?php

namespace Tests\Feature;

use App\Enums\UnitCategory;
use App\Models\Project;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StageDependencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_cannot_start_stage_if_previous_stage_not_completed()
    {
        $this->seed(\Database\Seeders\TemplateSeeder::class);
        $user = User::factory()->create();
        $project = Project::factory()->create();

        // Create unit and trigger stage generation
        $response = $this->actingAs($user)->postJson("/api/projects/{$project->id}/units", [
            'unit_type' => 'Company MonoSpace 700',
            'equipment_number' => 'DEP-001',
            'category' => UnitCategory::ELEVATOR,
        ]);

        $unitId = $response->json('data.id');
        $unit = Unit::find($unitId);

        // Get Stage 1 and Stage 2
        $stage1 = $unit->stages()->whereHas('template', fn ($q) => $q->where('stage_number', 1))->first();
        $stage2 = $unit->stages()->whereHas('template', fn ($q) => $q->where('stage_number', 2))->first();

        // Attempting to mark Stage 2 as in_progress should fail if Stage 1 is pending
        $response = $this->actingAs($user)->putJson("/api/stages/{$stage2->id}", [
            'status' => 'in_progress',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['status']);
        $this->assertEquals('Cannot start or complete this stage. Stage 1 (Plumbing) must be completed first.', $response->json('errors.status.0'));

        // Now mark Stage 1 as completed (by marking its tasks as pass)
        foreach ($stage1->tasks as $task) {
            $this->actingAs($user)->putJson("/api/tasks/{$task->id}", [
                'status' => 'pass',
            ]);
        }

        $this->assertEquals('completed', $stage1->fresh()->status);

        // Now Stage 2 should be startable
        $response = $this->actingAs($user)->putJson("/api/stages/{$stage2->id}", [
            'status' => 'in_progress',
        ]);

        $response->assertStatus(200);
        $this->assertEquals('in_progress', $stage2->fresh()->status);
    }

    public function test_cannot_complete_stage_manually_if_previous_stage_not_completed()
    {
        $this->seed(\Database\Seeders\TemplateSeeder::class);
        $user = User::factory()->create();
        $project = Project::factory()->create();

        $response = $this->actingAs($user)->postJson("/api/projects/{$project->id}/units", [
            'unit_type' => 'Company MonoSpace 700',
            'equipment_number' => 'DEP-002',
            'category' => UnitCategory::ELEVATOR,
        ]);

        $unit = Unit::find($response->json('data.id'));
        $stage2 = $unit->stages()->whereHas('template', fn ($q) => $q->where('stage_number', 2))->first();

        // Attempting to mark Stage 2 as completed should fail if Stage 1 is pending
        $response = $this->actingAs($user)->putJson("/api/stages/{$stage2->id}", [
            'status' => 'completed',
        ]);

        $response->assertStatus(422);
    }
}
