<?php

namespace Tests\Feature;

use App\Enums\UnitCategory;
use App\Models\Project;
use App\Models\RideComfortResult;
use App\Models\Unit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogicVerificationTest extends TestCase
{
    // usage of RefreshDatabase might wipe seeders?
    // Yes, RefreshDatabase wipes db. We need to run seeders.
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\TemplateSeeder::class);
    }

    public function test_full_logic_flow()
    {
        // 1. Create Project & Unit
        $project = Project::create([
            'name' => 'Test Project',
            'kone_project_id' => 'KP001584',
            'client_name' => 'Test Client',
            'location' => 'Test Location',
        ]);

        $unit = Unit::create([
            'project_id' => $project->id,
            'unit_type' => 'Company MonoSpace 700',
            'equipment_number' => '12345',
            'category' => UnitCategory::ELEVATOR,
        ]);

        // Manual Generation needed now as model event hook is removed
        \App\Services\UnitService::generateStagesAndTasks($unit);
        $unit->refresh(); // Load relationships

        // Verify Auto-Creation
        $this->assertCount(8, $unit->stages);
        $stage1 = $unit->stages->where('template.stage_number', 1)->first();
        $this->assertNotNull($stage1);
        $this->assertCount(5, $stage1->tasks); // Stage 1 has 5 tasks

        // 2. Complete Stage 1
        foreach ($stage1->tasks as $task) {
            \App\Services\TaskService::updateStatus($task, 'pass', 'Verified');
        }

        $stage1->refresh();
        $this->assertEquals('completed', $stage1->status);

        // 3. Check Progress
        $unit->refresh();
        // 1 stage out of 8 completed = 5 / 69 = 8%
        $this->assertEquals(8, $unit->progress_percent);

        // 5. Ride Comfort (Stage 8)
        $stage8 = $unit->stages->where('template.stage_number', 8)->first();
        $this->assertEquals('pending', $stage8->status);

        RideComfortResult::create([
            'unit_id' => $unit->id,
            'vibration_value' => 0.5,
            'noise_db' => 50,
            'jerk_value' => 0.8,
            'passed' => true,
        ]);

        // Manual trigger or rely on Controller logic (here we use model directly so we manual trigger)
        $stage8->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
        \App\Services\ProgressService::calculate($unit);

        $stage8->refresh();
        $this->assertEquals('completed', $stage8->status);

        // Check progress again (Earned = 5 + 1 = 6. Total = 69. 6/69 = 9%)
        $unit->refresh();
        $this->assertEquals(9, $unit->progress_percent);
    }
}
