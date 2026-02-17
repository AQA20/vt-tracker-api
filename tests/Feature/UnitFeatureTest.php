<?php

namespace Tests\Feature;

use App\Enums\UnitCategory;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UnitFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_unit_and_auto_generates_workflow()
    {
        $this->seed(\Database\Seeders\TemplateSeeder::class);
        $user = User::factory()->create();
        $project = Project::factory()->create();

        $response = $this->actingAs($user)->postJson("/api/projects/{$project->id}/units", [
            'unit_type' => 'Company MonoSpace 700',
            'equipment_number' => 'FT-001',
            'category' => UnitCategory::ELEVATOR,
        ]);

        $response->assertStatus(201);
        $unitId = $response->json('data.id');

        // Check DB side effects
        $this->assertDatabaseHas('units', ['id' => $unitId, 'equipment_number' => 'FT-001']);
        $this->assertDatabaseHas('unit_stages', ['unit_id' => $unitId]);
    }
}
