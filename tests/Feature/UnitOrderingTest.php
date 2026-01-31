<?php

namespace Tests\Feature;

use App\Enums\UnitCategory;
use App\Models\Project;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UnitOrderingTest extends TestCase
{
    use RefreshDatabase;

    public function test_units_are_ordered_by_equipment_number_in_controller_index()
    {
        $user = User::factory()->create();
        $project = Project::factory()->create();

        // Create units in random order
        Unit::factory()->create([
            'project_id' => $project->id,
            'equipment_number' => 'C',
            'unit_type' => 'Type 1',
            'category' => UnitCategory::ELEVATOR,
        ]);
        Unit::factory()->create([
            'project_id' => $project->id,
            'equipment_number' => 'A',
            'unit_type' => 'Type 1',
            'category' => UnitCategory::ELEVATOR,
        ]);
        Unit::factory()->create([
            'project_id' => $project->id,
            'equipment_number' => 'B',
            'unit_type' => 'Type 1',
            'category' => UnitCategory::ELEVATOR,
        ]);

        $response = $this->actingAs($user)->getJson("/api/projects/{$project->id}/units");

        $response->assertStatus(200);
        $units = $response->json();

        $this->assertCount(3, $units);
        $this->assertEquals('A', $units[0]['equipment_number']);
        $this->assertEquals('B', $units[1]['equipment_number']);
        $this->assertEquals('C', $units[2]['equipment_number']);
    }

    public function test_units_are_ordered_by_equipment_number_in_project_relationship()
    {
        $project = Project::factory()->create();

        // Create units in random order
        Unit::factory()->create([
            'project_id' => $project->id,
            'equipment_number' => 'Z',
            'unit_type' => 'Type 1',
            'category' => UnitCategory::ELEVATOR,
        ]);
        Unit::factory()->create([
            'project_id' => $project->id,
            'equipment_number' => 'X',
            'unit_type' => 'Type 1',
            'category' => UnitCategory::ELEVATOR,
        ]);
        Unit::factory()->create([
            'project_id' => $project->id,
            'equipment_number' => 'Y',
            'unit_type' => 'Type 1',
            'category' => UnitCategory::ELEVATOR,
        ]);

        $units = $project->refresh()->units;

        $this->assertEquals('X', $units[0]->equipment_number);
        $this->assertEquals('Y', $units[1]->equipment_number);
        $this->assertEquals('Z', $units[2]->equipment_number);
    }
}
