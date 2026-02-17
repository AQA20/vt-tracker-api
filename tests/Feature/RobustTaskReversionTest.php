<?php

namespace Tests\Feature;

use App\Enums\UnitCategory;
use App\Models\Project;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RobustTaskReversionTest extends TestCase
{
    use RefreshDatabase;

    public function test_task_reversion_is_blocked_by_subsequent_work_in_same_group()
    {
        $this->seed(\Database\Seeders\TemplateSeeder::class);
        $user = User::factory()->create();
        $project = Project::factory()->create();

        $response = $this->actingAs($user)->postJson("/api/projects/{$project->id}/units", [
            'unit_type' => 'Company MonoSpace 700',
            'equipment_number' => 'REVERT-01',
            'category' => UnitCategory::ELEVATOR,
        ]);

        $unit = Unit::find($response->json('data.id'));

        // Installation: Stages 1-6
        // Commissioning: Stages 7-8

        // Complete Stage 1 to 5 to allow Stage 6 work
        for ($i = 1; $i <= 5; $i++) {
            $stage = $unit->stages()->whereHas('template', fn ($q) => $q->where('stage_number', $i))->first();
            $this->actingAs($user)->putJson("/api/stages/{$stage->id}", ['status' => 'completed'])->assertStatus(200);
        }

        $stage1 = $unit->stages()->whereHas('template', fn ($q) => $q->where('stage_number', 1))->first();
        $stage6 = $unit->stages()->whereHas('template', fn ($q) => $q->where('stage_number', 6))->first();
        $stage7 = $unit->stages()->whereHas('template', fn ($q) => $q->where('stage_number', 7))->first();
        $stage8 = $unit->stages()->whereHas('template', fn ($q) => $q->where('stage_number', 8))->first();

        // 1. Task in Stage 1 should be blocked from reversion if Task in Stage 6 is pass
        // Use the LAST task of Stage 1 to skip same-stage blocking checks
        $stage1Tasks = $stage1->tasks()
            ->join('task_templates', 'unit_tasks.task_template_id', '=', 'task_templates.id')
            ->select('unit_tasks.*')
            ->orderBy('task_templates.order_index')
            ->get();
        $task1Last = $stage1Tasks->last();
        $task6 = $stage6->tasks()->first();
        $task6Title = $task6->template->title;

        $this->actingAs($user)->putJson("/api/tasks/{$task6->id}", ['status' => 'pass'])->assertStatus(200);

        $response = $this->actingAs($user)->putJson("/api/tasks/{$task1Last->id}", ['status' => 'pending']);
        $response->assertStatus(422);
        // Stage 1 task is blocked by Stage 2 which is completed
        $this->assertEquals('Cannot mark task as incomplete because Stage 2 (First Guide Rails) is already completed.', $response->json('errors.error.0'));

        // 2. Task in Stage 1 should be blocked from reversion if Stage 6 is marked as completed (even if task6 is cleared)
        $this->actingAs($user)->putJson("/api/tasks/{$task6->id}", ['status' => 'pending'])->assertStatus(200);
        $this->actingAs($user)->putJson("/api/stages/{$stage6->id}", ['status' => 'completed'])->assertStatus(200);

        $response = $this->actingAs($user)->putJson("/api/tasks/{$task1Last->id}", ['status' => 'pending']);
        $response->assertStatus(422);
        $this->assertEquals('Cannot mark task as incomplete because Stage 2 (First Guide Rails) is already completed.', $response->json('errors.error.0'));

        // 3. Reverting task in Stage 1 should NOT be blocked by work in Commissioning group (Stage 7)
        // BUT WAIT: Before uncompleting Stage 1, we must uncomplete Stage 6 (and its tasks will auto-reset)
        $this->actingAs($user)->putJson("/api/stages/{$stage6->id}", ['status' => 'pending'])->assertStatus(200);

        // Verify stage 6 tasks were reset
        $task6->refresh();
        $this->assertEquals('pending', $task6->status);

        // Now clear all subsequent installation stages (2-5) in REVERSE order
        for ($i = 5; $i >= 2; $i--) {
            $stage = $unit->stages()->whereHas('template', fn ($q) => $q->where('stage_number', $i))->first();
            $this->actingAs($user)->putJson("/api/stages/{$stage->id}", ['status' => 'pending'])->assertStatus(200);
        }

        $task7 = $stage7->tasks()->first();
        $this->actingAs($user)->putJson("/api/tasks/{$task7->id}", ['status' => 'pass'])->assertStatus(200);

        $response = $this->actingAs($user)->putJson("/api/tasks/{$task1Last->id}", ['status' => 'pending']);
        $response->assertStatus(200);

        // 4. Reverting task in Stage 7 should be blocked by manually completed Stage 8 (no tasks)
        // Must complete Stage 7 fully first to allow Stage 8 completion
        $stage7Tasks = $stage7->tasks()
            ->join('task_templates', 'unit_tasks.task_template_id', '=', 'task_templates.id')
            ->select('unit_tasks.*')
            ->orderBy('task_templates.order_index')
            ->get();
        foreach ($stage7Tasks as $t) {
            $this->actingAs($user)->putJson("/api/tasks/{$t->id}", ['status' => 'pass'])->assertStatus(200);
        }
        $this->actingAs($user)->putJson("/api/stages/{$stage8->id}", ['status' => 'completed'])->assertStatus(200);

        // Reverting the LAST task of Stage 7 should be blocked by Stage 8 (since no later tasks in Stage 7)
        $lastTask7 = $stage7Tasks->last();
        $response = $this->actingAs($user)->putJson("/api/tasks/{$lastTask7->id}", ['status' => 'pending']);
        $response->assertStatus(422);
        $this->assertEquals('Cannot mark task as incomplete because Stage 8 (Ride Comfort) is already completed.', $response->json('errors.error.0'));
    }
}
