<?php

namespace Tests\Unit\Services;

use App\Models\Project;
use App\Models\Unit;
use App\Enums\UnitCategory;
use App\Services\TaskService;
use App\Services\UnitService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Validation\ValidationException;

class TaskServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_update_status_updates_task()
    {
        $this->seed(\Database\Seeders\TemplateSeeder::class);
        $project = Project::factory()->create(['name' => 'Test Project', 'client_name' => 'Test Client', 'location' => 'Test Loc']);
        $unit = Unit::create(['project_id' => $project->id, 'unit_type' => 'KONE MonoSpace 700', 'equipment_number' => 'U-001', 'category' => UnitCategory::ELEVATOR]);
        UnitService::generateStagesAndTasks($unit);

        $stage1 = $unit->stages->where('template.stage_number', 1)->first();
        $task = $stage1->tasks->first();

        TaskService::updateStatus($task, 'pass', 'Sample Note');

        $this->assertEquals('pass', $task->fresh()->status);
        $this->assertEquals('Sample Note', $task->fresh()->notes);
    }
}
