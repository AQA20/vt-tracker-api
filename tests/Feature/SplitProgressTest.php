<?php

namespace Tests\Feature;

use App\Enums\UnitCategory;
use App\Models\Project;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SplitProgressTest extends TestCase
{
    use RefreshDatabase;

    public function test_elevator_progress_is_split_into_installation_and_commissioning()
    {
        $this->seed(\Database\Seeders\TemplateSeeder::class);
        $user = User::factory()->create();
        $project = Project::factory()->create();

        // Create an Elevator unit
        $response = $this->actingAs($user)->postJson("/api/projects/{$project->id}/units", [
            'unit_type' => 'Company MonoSpace 700',
            'equipment_number' => 'SPLIT-001',
            'category' => UnitCategory::ELEVATOR,
        ]);

        $unitId = $response->json('data.id');
        $unit = Unit::find($unitId);

        // Initially all progress is 0%
        $this->assertEquals(0, $unit->progress_percent);
        $this->assertEquals(0, $unit->installation_progress);
        $this->assertEquals(0, $unit->commissioning_progress);

        // 1. Complete a task in Stage 1 (Installation)
        $stage1 = $unit->stages()->whereHas('template', fn ($q) => $q->where('stage_number', 1))->first();
        $task1 = $stage1->tasks()->first();

        $this->actingAs($user)->putJson("/api/tasks/{$task1->id}", ['status' => 'pass']);

        $unit->refresh();
        $project->refresh();

        $this->assertGreaterThan(0, $unit->installation_progress);
        $this->assertEquals(0, $unit->commissioning_progress);
        $this->assertGreaterThan(0, $unit->progress_percent);

        // Project level
        $this->assertGreaterThan(0, $project->installation_progress);
        $this->assertEquals(0, $project->commissioning_progress);

        // 2. Complete Stage 1 and Stage 7 (Commissioning)
        // To complete Stage 1, we need to pass all its tasks.
        foreach ($stage1->tasks as $task) {
            $this->actingAs($user)->putJson("/api/tasks/{$task->id}", ['status' => 'pass']);
        }

        // Before Stage 7, we must complete Stage 1-6 due to dependency enforcement
        for ($i = 2; $i <= 6; $i++) {
            $stage = $unit->stages()->whereHas('template', fn ($q) => $q->where('stage_number', $i))->first();
            foreach ($stage->tasks as $task) {
                $this->actingAs($user)->putJson("/api/tasks/{$task->id}", ['status' => 'pass']);
            }
        }

        $unit->refresh();
        $this->assertEquals(100, $unit->installation_progress);
        $this->assertEquals(0, $unit->commissioning_progress);

        // Now Stage 7 Tasks
        $stage7 = $unit->stages()->whereHas('template', fn ($q) => $q->where('stage_number', 7))->first();
        $task7 = $stage7->tasks()->first();

        $this->actingAs($user)->putJson("/api/tasks/{$task7->id}", ['status' => 'pass']);

        $unit->refresh();
        $project->refresh();

        $this->assertEquals(100, $unit->installation_progress);
        $this->assertGreaterThan(0, $unit->commissioning_progress);

        // Project level
        $this->assertEquals(100, $project->installation_progress);
        $this->assertGreaterThan(0, $project->commissioning_progress);
    }
}
