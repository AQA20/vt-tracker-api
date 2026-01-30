<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Unit;
use App\Enums\UnitCategory;
use App\Models\UnitStage;
use App\Services\UnitService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StageToggleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\TemplateSeeder::class);
    }

    public function test_can_manually_toggle_stage_completion()
    {
        $user = User::factory()->create();
        
        $project = Project::create([
            'name' => 'Test Project',
            'client_name' => 'Test Client',
            'location' => 'Test Location'
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
        $this->assertEquals('pending', $stage1->status);

        // 1. Mark Stage 1 as completed
        $response = $this->actingAs($user)
            ->putJson("/api/stages/{$stage1->id}", [
                'status' => 'completed'
            ]);

        $response->assertStatus(200);
        $stage1->refresh();
        $this->assertEquals('completed', $stage1->status);
        $this->assertNotNull($stage1->completed_at);

        // 2. Check Unit Progress (S1 has 5 tasks. Total tasks = 69. 5/69 = 7.2% -> 8%)
        $unit->refresh();
        $this->assertEquals(8, $unit->progress_percent);

        // 3. Mark Stage 1 back to pending
        $response = $this->actingAs($user)
            ->putJson("/api/stages/{$stage1->id}", [
                'status' => 'pending'
            ]);

        $response->assertStatus(200);
        $stage1->refresh();
        $this->assertEquals('pending', $stage1->status);
        $this->assertNull($stage1->completed_at);

        // 4. Check Unit Progress (0 stages completed = 0%)
        $unit->refresh();
        $this->assertEquals(0, $unit->progress_percent);
    }
}
