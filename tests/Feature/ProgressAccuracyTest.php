<?php

namespace Tests\Feature;

use App\Enums\UnitCategory;
use App\Models\Project;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProgressAccuracyTest extends TestCase
{
    use RefreshDatabase;

    public function test_progress_is_accurate_and_caps_at_99_when_one_task_is_missing()
    {
        $this->seed(\Database\Seeders\TemplateSeeder::class);
        $user = User::factory()->create();
        $project = Project::factory()->create();

        // Create an Elevator unit
        $response = $this->actingAs($user)->postJson("/api/projects/{$project->id}/units", [
            'unit_type' => 'KONE MonoSpace 700',
            'equipment_number' => 'ACC-001',
            'category' => UnitCategory::ELEVATOR,
        ]);

        $unit = Unit::find($response->json('id'));

        // Complete all tasks in ALL stages (1-8) EXCEPT the very last one
        $allStages = $unit->stages()->with('tasks')->get();

        $allTasks = [];
        foreach ($allStages as $stage) {
            foreach ($stage->tasks as $task) {
                $allTasks[] = $task;
            }
        }

        $lastTask = array_pop($allTasks);

        foreach ($allTasks as $task) {
            $this->actingAs($user)->putJson("/api/tasks/{$task->id}", ['status' => 'pass']);
        }

        $unit->refresh();
        $project->refresh();

        // Installation group should be 100% (since all its tasks were completed)
        $this->assertEquals(100, $unit->installation_progress);

        // Overall unit and project should be high but EXPLICITLY NOT 100%
        $this->assertLessThan(100, $unit->progress_percent);
        $this->assertGreaterThan(90, $unit->progress_percent);

        $this->assertLessThan(100, $project->completion_percentage);
        $this->assertGreaterThan(90, $project->completion_percentage);

        // Stage progress should also be capped if incomplete
        $this->assertLessThan(100, $lastTask->unitStage->progress_percent);

        // Now complete that last task
        $this->actingAs($user)->putJson("/api/tasks/{$lastTask->id}", ['status' => 'pass']);

        // Stage 8 has no tasks, so it must be completed manually (or it's a special stage)
        $stage8 = $unit->stages()->whereHas('template', fn ($q) => $q->where('stage_number', 8))->first();
        $this->actingAs($user)->putJson("/api/stages/{$stage8->id}", ['status' => 'completed']);

        $unit->refresh();
        $project->refresh();

        $this->assertEquals(100, $unit->installation_progress);
        $this->assertEquals(100, $project->installation_progress);
        $this->assertEquals(100, $lastTask->unitStage->fresh()->progress_percent);
        $this->assertEquals(100, $project->completion_percentage);
    }
}
