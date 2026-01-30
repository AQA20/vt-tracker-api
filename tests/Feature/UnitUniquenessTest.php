<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Unit;
use App\Models\User;
use App\Enums\UnitCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UnitUniquenessTest extends TestCase
{
    use RefreshDatabase;

    public function test_cannot_create_unit_with_duplicate_equipment_number()
    {
        $user = User::factory()->create();
        $project = Project::factory()->create();

        Unit::factory()->create([
            'equipment_number' => 'DUPE-001',
            'project_id' => $project->id,
        ]);

        $response = $this->actingAs($user)->postJson("/api/projects/{$project->id}/units", [
            'unit_type' => 'KONE MonoSpace 700',
            'equipment_number' => 'DUPE-001',
            'category' => UnitCategory::ELEVATOR,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['equipment_number']);
    }

    public function test_cannot_update_unit_to_duplicate_equipment_number()
    {
        $user = User::factory()->create();
        $project = Project::factory()->create();

        $unit1 = Unit::factory()->create([
            'equipment_number' => 'UNIQUE-001',
            'project_id' => $project->id,
        ]);

        Unit::factory()->create([
            'equipment_number' => 'DUPE-001',
            'project_id' => $project->id,
        ]);

        $response = $this->actingAs($user)->putJson("/api/units/{$unit1->id}", [
            'equipment_number' => 'DUPE-001',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['equipment_number']);
    }

    public function test_can_update_unit_keeping_its_own_equipment_number()
    {
        $user = User::factory()->create();
        $project = Project::factory()->create();

        $unit = Unit::factory()->create([
            'equipment_number' => 'OWN-001',
            'project_id' => $project->id,
        ]);

        $response = $this->actingAs($user)->putJson("/api/units/{$unit->id}", [
            'equipment_number' => 'OWN-001',
            'category' => UnitCategory::ESCALATOR,
        ]);

        $response->assertStatus(200);
        $this->assertEquals('OWN-001', $unit->fresh()->equipment_number);
        $this->assertEquals(UnitCategory::ESCALATOR, $unit->fresh()->category);
    }
}
