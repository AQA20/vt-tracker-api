<?php

namespace Tests\Unit\Services;

use App\Enums\UnitCategory;
use App\Models\Project;
use App\Models\Unit;
use App\Services\StageService;
use App\Services\UnitService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StageServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_start_stage_enforces_dependencies()
    {
        $this->seed(\Database\Seeders\TemplateSeeder::class);
        $project = Project::factory()->create(['name' => 'Test Project', 'client_name' => 'Test Client', 'location' => 'Test Loc']);
        $unit = Unit::create(['project_id' => $project->id, 'unit_type' => 'Company MonoSpace 700', 'equipment_number' => 'U-001', 'category' => UnitCategory::ELEVATOR]);
        UnitService::generateStagesAndTasks($unit); // Use service to setup

        $stage7 = $unit->stages->where('template.stage_number', 7)->first();

        // Stage 7 (Commissioning) is independent of Stage 6 (Installation), so it should be TRUE
        $this->assertTrue(StageService::canStartStage($stage7));

        // Complete Stage 6
        $stage6 = $unit->stages->where('template.stage_number', 6)->first();
        $stage6->update(['status' => 'completed']);

        // Should be true now
        $this->assertTrue(StageService::canStartStage($stage7));
    }

    public function test_check_stage_completion_completes_stage()
    {
        $this->seed(\Database\Seeders\TemplateSeeder::class);
        $project = Project::factory()->create(['name' => 'Test Project', 'client_name' => 'Test Client', 'location' => 'Test Loc']);
        $unit = Unit::create(['project_id' => $project->id, 'unit_type' => 'Company MonoSpace 700', 'equipment_number' => 'U-001', 'category' => UnitCategory::ELEVATOR]);
        UnitService::generateStagesAndTasks($unit);

        $stage1 = $unit->stages->where('template.stage_number', 1)->first();

        // Pass all tasks
        foreach ($stage1->tasks as $task) {
            $task->update(['status' => 'pass', 'measured_value' => 0.5]);
        }

        StageService::checkStageCompletion($stage1);

        $this->assertEquals('completed', $stage1->fresh()->status);
    }
}
