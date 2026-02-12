<?php

namespace Tests\Feature;

use App\Enums\UnitCategory;
use App\Models\Project;
use App\Models\Unit;
use App\Services\TaskService;
use App\Services\UnitService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class TaskReversionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\TemplateSeeder::class);
    }

    public function test_cannot_mark_task_as_incomplete_if_next_stage_is_completed()
    {
        // 1. Setup Project, Unit, Stages and Tasks
        $project = Project::create([
            'name' => 'Test Project',
            'kone_project_id' => 'KP001584',
            'client_name' => 'Test Client',
            'location' => 'Test Location',
        ]);

        $unit = Unit::create([
            'project_id' => $project->id,
            'unit_type' => 'KONE MonoSpace 700',
            'equipment_number' => '12345',
            'category' => UnitCategory::ELEVATOR,
        ]);

        UnitService::generateStagesAndTasks($unit);
        $unit->refresh();

        $stage1 = $unit->stages->where('template.stage_number', 1)->first();
        $stage2 = $unit->stages->where('template.stage_number', 2)->first();

        // 2. Complete all tasks in Stage 1
        foreach ($stage1->tasks as $task) {
            TaskService::updateStatus($task, 'pass');
        }
        $stage1->refresh();
        $this->assertEquals('completed', $stage1->status);

        // 3. Complete all tasks in Stage 2
        foreach ($stage2->tasks as $task) {
            TaskService::updateStatus($task, 'pass');
        }
        $stage2->refresh();
        $this->assertEquals('completed', $stage2->status);

        // 4. Try to mark the LAST task in Stage 1 as 'pending'
        // Since it's the last task in this stage, it has no subsequent tasks in this stage,
        // but it has subsequent tasks in Stage 2 (which is completed), so it should fail correctly.
        $lastTaskStage1 = $stage1->tasks->sortBy(fn ($t) => $t->template->order_index)->last();

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Cannot mark task as incomplete because Stage 2 (First Guide Rails) is already completed.');

        TaskService::updateStatus($lastTaskStage1, 'pending');
    }

    public function test_cannot_mark_task_as_incomplete_if_later_task_in_same_stage_is_completed()
    {
        // 1. Setup Project, Unit, Stages and Tasks
        $project = Project::create([
            'name' => 'Test Project',
            'kone_project_id' => 'KP001584',
            'client_name' => 'Test Client',
            'location' => 'Test Location',
        ]);

        $unit = Unit::create([
            'project_id' => $project->id,
            'unit_type' => 'KONE MonoSpace 700',
            'equipment_number' => 'SAME-STAGE',
            'category' => UnitCategory::ELEVATOR,
        ]);

        UnitService::generateStagesAndTasks($unit);
        $unit->refresh();

        $stage1 = $unit->stages->where('template.stage_number', 1)->first();
        $task1 = $stage1->tasks->sortBy(fn ($t) => $t->template->order_index)->get(0);
        $task2 = $stage1->tasks->sortBy(fn ($t) => $t->template->order_index)->get(1);

        // 2. Complete Task 1 and Task 2
        TaskService::updateStatus($task1, 'pass');
        TaskService::updateStatus($task2, 'pass');

        // 3. Try to mark Task 1 as 'pending'
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Cannot mark task as incomplete because Task 1.2 (Check shaft verticality) is already completed.');

        TaskService::updateStatus($task1, 'pending');
    }

    public function test_can_revert_last_task_in_stage()
    {
        // 1. Setup
        $project = Project::create(['name' => 'Test', 'kone_project_id' => 'KP001584', 'client_name' => 'T', 'location' => 'L']);
        $unit = Unit::create(['project_id' => $project->id, 'unit_type' => 'KONE MonoSpace 700', 'equipment_number' => 'REVERT-LAST', 'category' => UnitCategory::ELEVATOR]);
        UnitService::generateStagesAndTasks($unit);
        $stage1 = $unit->refresh()->stages->where('template.stage_number', 1)->first();

        // 2. Complete Stage 1
        foreach ($stage1->tasks as $task) {
            TaskService::updateStatus($task, 'pass');
        }
        $this->assertEquals('completed', $stage1->refresh()->status);

        // 3. Mark the LAST task as 'pending' - should work
        $lastTask = $stage1->tasks->sortByDesc(fn ($t) => $t->template->order_index)->first();
        TaskService::updateStatus($lastTask, 'pending');

        $this->assertEquals('pending', $lastTask->refresh()->status);
        $this->assertNotEquals('completed', $stage1->refresh()->status);
    }

    public function test_must_revert_tasks_in_reverse_order()
    {
        // 1. Setup
        $project = Project::create(['name' => 'Test', 'kone_project_id' => 'KP001584', 'client_name' => 'T', 'location' => 'L']);
        $unit = Unit::create(['project_id' => $project->id, 'unit_type' => 'KONE MonoSpace 700', 'equipment_number' => 'REVERSE', 'category' => UnitCategory::ELEVATOR]);
        UnitService::generateStagesAndTasks($unit);
        $stage1 = $unit->refresh()->stages->where('template.stage_number', 1)->first();
        $tasks = $stage1->tasks->sortBy(fn ($t) => $t->template->order_index);
        $t1 = $tasks->get(0);
        $t2 = $tasks->get(1);

        TaskService::updateStatus($t1, 'pass');
        TaskService::updateStatus($t2, 'pass');

        // Try revert t1 -> fail
        try {
            TaskService::updateStatus($t1, 'pending');
            $this->fail('Should have thrown exception');
        } catch (ValidationException $e) {
            $this->assertEquals('Cannot mark task as incomplete because Task 1.2 (Check shaft verticality) is already completed.', $e->getMessage());
        }

        // Revert t2 -> success
        TaskService::updateStatus($t2, 'pending');
        $this->assertEquals('pending', $t2->refresh()->status);

        // Now revert t1 -> success
        TaskService::updateStatus($t1, 'pending');
        $this->assertEquals('pending', $t1->refresh()->status);
    }
}
