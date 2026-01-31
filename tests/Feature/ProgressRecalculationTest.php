<?php

namespace Tests\Feature;

use App\Enums\UnitCategory;
use App\Models\Project;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProgressRecalculationTest extends TestCase
{
    use RefreshDatabase;

    public function test_unit_progress_recalculates_on_task_status_change()
    {
        $this->seed(\Database\Seeders\TemplateSeeder::class);
        $user = User::factory()->create();
        $project = Project::factory()->create();

        // Create unit and trigger stage/task generation
        $response = $this->actingAs($user)->postJson("/api/projects/{$project->id}/units", [
            'unit_type' => 'KONE MonoSpace 700',
            'equipment_number' => 'RE-101',
            'category' => UnitCategory::ELEVATOR,
        ]);

        $unitId = $response->json('id');
        $unit = Unit::find($unitId);

        // Initially 0%
        $this->assertEquals(0, $unit->progress_percent);

        // Get the first task
        $firstStage = $unit->stages()->orderBy('id')->first();
        $firstTask = $firstStage->tasks()->first();

        // Mark task as pass
        $this->actingAs($user)->putJson("/api/tasks/{$firstTask->id}", [
            'status' => 'pass',
        ]);

        // Check unit progress
        $unit->refresh();
        $this->assertGreaterThan(0, $unit->progress_percent);

        // Project completion percentage should also be > 0
        $this->assertGreaterThan(0, $project->fresh()->completion_percentage);
    }
}
