<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Unit;
use App\Enums\UnitCategory;
use App\Models\User;
use App\Services\UnitService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RideComfortFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_cannot_submit_ride_comfort_if_stage_7_incomplete()
    {
        $this->seed(\Database\Seeders\TemplateSeeder::class);
        $user = User::factory()->create();
        $project = Project::factory()->create();
        $unit = Unit::create(['project_id' => $project->id, 'unit_type' => 'KONE MonoSpace 700', 'equipment_number' => 'RC-001', 'category' => UnitCategory::ELEVATOR]);
        UnitService::generateStagesAndTasks($unit);

        // Stage 7 is pending by default
        
        $response = $this->actingAs($user)->postJson("/api/units/{$unit->id}/ride-comfort", [
            'vibration_value' => 0.5,
            'noise_db' => 50,
            'jerk_value' => 0.8,
        ]);

        $response->assertStatus(422); // Validation error
        $response->assertJsonValidationErrors(['error']);
    }

    public function test_can_submit_ride_comfort_if_stage_7_complete()
    {
        $this->seed(\Database\Seeders\TemplateSeeder::class);
        $user = User::factory()->create();
        $project = Project::factory()->create();
        $unit = Unit::create(['project_id' => $project->id, 'unit_type' => 'KONE MonoSpace 700', 'equipment_number' => 'RC-001', 'category' => UnitCategory::ELEVATOR]);
        UnitService::generateStagesAndTasks($unit);

        // Complete Stage 7
        $stage7 = $unit->stages()->whereHas('template', fn($q) => $q->where('stage_number', 7))->first();
        $stage7->update(['status' => 'completed']);
        
        $response = $this->actingAs($user)->postJson("/api/units/{$unit->id}/ride-comfort", [
            'vibration_value' => 0.5,
            'noise_db' => 50,
            'jerk_value' => 0.8,
            'device_used' => 'kone_ride_check',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('ride_comfort_results', ['unit_id' => $unit->id]);
        
        // Check Sage 8 completion
        $stage8 = $unit->stages()->whereHas('template', fn($q) => $q->where('stage_number', 8))->first();
        $this->assertEquals('completed', $stage8->fresh()->status);
    }

    public function test_cannot_autocomplete_ride_comfort_if_failed()
    {
        $this->seed(\Database\Seeders\TemplateSeeder::class);
        $user = User::factory()->create();
        $project = Project::factory()->create();
        $unit = Unit::create(['project_id' => $project->id, 'unit_type' => 'KONE MonoSpace 700', 'equipment_number' => 'RC-001', 'category' => UnitCategory::ELEVATOR]);
        UnitService::generateStagesAndTasks($unit);

        // Complete Stage 7
        $stage7 = $unit->stages()->whereHas('template', fn($q) => $q->where('stage_number', 7))->first();
        $stage7->update(['status' => 'completed']);
        
        // Submit failing result (vibration > 1.0)
        $response = $this->actingAs($user)->postJson("/api/units/{$unit->id}/ride-comfort", [
            'vibration_value' => 2.0,
            'noise_db' => 50,
            'jerk_value' => 0.8,
            'device_used' => 'eva_625',
        ]);

        $response->assertStatus(201);
        $this->assertEquals(false, $response->json('passed'));
        $this->assertEquals('eva_625', $response->json('device_used'));
        
        // Stage 8 should NOT be completed
        $stage8 = $unit->stages()->whereHas('template', fn($q) => $q->where('stage_number', 8))->first();
        $this->assertNotEquals('completed', $stage8->fresh()->status);
    }
}
