<?php

namespace Tests\Unit\Services;

use App\Enums\UnitCategory;
use App\Models\Project;
use App\Models\Unit;
use App\Services\UnitService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UnitServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_generate_stages_and_tasks_creates_correct_structure()
    {
        $this->seed(\Database\Seeders\TemplateSeeder::class);

        $project = Project::factory()->create(['name' => 'Test Project', 'client_name' => 'Test Client', 'location' => 'Test Loc']);
        $unit = Unit::create([
            'project_id' => $project->id,
            'unit_type' => 'Company MonoSpace 700',
            'equipment_number' => 'U-001',
            'category' => UnitCategory::ELEVATOR,
        ]);

        UnitService::generateStagesAndTasks($unit);

        $this->assertCount(8, $unit->stages);
        $stage1 = $unit->stages()->whereHas('template', function ($q) {
            $q->where('stage_number', 1);
        })->first();
        $this->assertNotNull($stage1);
        $this->assertTrue($stage1->tasks()->count() > 0);
    }

    public function test_generate_stages_and_tasks_filters_by_category()
    {
        $this->seed(\Database\Seeders\TemplateSeeder::class);

        $project = Project::factory()->create(['name' => 'Test Project', 'client_name' => 'Test Client', 'location' => 'Test Loc']);
        // Use a different category
        $unit = Unit::create([
            'project_id' => $project->id,
            'unit_type' => 'Company MonoSpace 700',
            'equipment_number' => 'U-001',
            'category' => UnitCategory::ESCALATOR,
        ]);

        UnitService::generateStagesAndTasks($unit);

        // Should have 0 stages because TemplateSeeder only has elevator templates
        $this->assertCount(0, $unit->stages);
    }
}
