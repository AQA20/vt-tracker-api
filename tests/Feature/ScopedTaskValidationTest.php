<?php

namespace Tests\Feature;

use App\Enums\UnitCategory;
use App\Models\Project;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScopedTaskValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_installation_and_commissioning_groups_are_validated_independently()
    {
        $this->seed(\Database\Seeders\TemplateSeeder::class);
        $user = User::factory()->create();
        $project = Project::factory()->create();

        // Create an Elevator unit
        $response = $this->actingAs($user)->postJson("/api/projects/{$project->id}/units", [
            'unit_type' => 'KONE MonoSpace 700',
            'equipment_number' => 'SCOPE-001',
            'category' => UnitCategory::ELEVATOR,
        ]);

        $unit = Unit::find($response->json('id'));

        // Installation: Stages 1-6
        // Commissioning: Stages 7-8

        $stage1 = $unit->stages()->whereHas('template', fn ($q) => $q->where('stage_number', 1))->first();
        $stage5 = $unit->stages()->whereHas('template', fn ($q) => $q->where('stage_number', 5))->first();
        $stage6 = $unit->stages()->whereHas('template', fn ($q) => $q->where('stage_number', 6))->first();
        $stage7 = $unit->stages()->whereHas('template', fn ($q) => $q->where('stage_number', 7))->first();
        $stage8 = $unit->stages()->whereHas('template', fn ($q) => $q->where('stage_number', 8))->first();

        // 1. Commissioning Stage 7 should be startable even if Installation Stage 1 is NOT completed
        $response = $this->actingAs($user)->putJson("/api/stages/{$stage7->id}", ['status' => 'in_progress']);
        $response->assertStatus(200);

        // 2. Commissioning Stage 8 should NOT be startable because Stage 7 is NOT completed
        $response = $this->actingAs($user)->putJson("/api/stages/{$stage8->id}", ['status' => 'in_progress']);
        $response->assertStatus(422);

        // 3. Complete Stages 1 to 5 sequentially to satisfy dependencies
        for ($i = 1; $i <= 5; $i++) {
            $stage = $unit->stages()->whereHas('template', fn ($q) => $q->where('stage_number', $i))->first();
            $this->actingAs($user)->putJson("/api/stages/{$stage->id}", ['status' => 'completed'])->assertStatus(200);
        }

        // 4. Marking Stage 1 task as incomplete should fail if Stage 6 task is completed (same group)
        $task6 = $stage6->tasks()->first();
        $this->actingAs($user)->putJson("/api/tasks/{$task6->id}", ['status' => 'pass'])->assertStatus(200);

        // Try to revert Stage 1 task. It should fail because Stage 6 task (same group) is pass.
        // Use the LAST task of the stage to avoid same-stage blocking (as Stage 1-5 were completed via Controller)
        $task1Last = $stage1->tasks()->join('task_templates', 'unit_tasks.task_template_id', '=', 'task_templates.id')
            ->select('unit_tasks.*')->orderByDesc('task_templates.order_index')->first();
        $response = $this->actingAs($user)->putJson("/api/tasks/{$task1Last->id}", ['status' => 'pending']);
        $response->assertStatus(422);

        // 5. Reverting Stage 1 task should NOT be blocked by Stage 7 task (different group)
        // Mark Stage 7 task as pass
        $task7 = $stage7->tasks()->first();
        $this->actingAs($user)->putJson("/api/tasks/{$task7->id}", ['status' => 'pass']);

        // First, clear Stage 6 tasks AND intermediate stages (2-5) in REVERSE order
        $this->actingAs($user)->putJson("/api/tasks/{$task6->id}", ['status' => 'pending'])->assertStatus(200);
        for ($i = 5; $i >= 2; $i--) {
            $stage = $unit->stages()->whereHas('template', fn ($q) => $q->where('stage_number', $i))->first();
            $this->actingAs($user)->putJson("/api/stages/{$stage->id}", ['status' => 'pending'])->assertStatus(200);
        }

        // Reverting Stage 1 task should now succeed despite Stage 7 being passed
        $response = $this->actingAs($user)->putJson("/api/tasks/{$task1Last->id}", ['status' => 'pending']);
        $response->assertStatus(200);
    }
}
