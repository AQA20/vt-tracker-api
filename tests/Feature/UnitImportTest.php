<?php

namespace Tests\Feature;

use App\Enums\UnitCategory;
use App\Models\Project;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class UnitImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_bulk_import_returns_summary_and_failed_rows(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create();

        Unit::factory()->create([
            'project_id' => $project->id,
            'equipment_number' => 'EXISTING-001',
        ]);

        $csv = implode("\n", [
            'equipment_number,sl_reference_no,fl_unit_name,unit_description,unit_type,category',
            'NEW-001,REF-01,Unit A,Good row,Company MonoSpace 700,elevator',
            'EXISTING-001,REF-02,Unit B,Duplicate in DB,Company MonoSpace 700,elevator',
            'NEW-001,REF-03,Unit C,Duplicate in file,Company MonoSpace 700,elevator',
            'BAD-TYPE,REF-04,Unit D,Invalid type,Unknown Type,elevator',
        ]);

        $file = UploadedFile::fake()->createWithContent('units.csv', $csv);

        $response = $this->actingAs($user)->post('/api/projects/'.$project->id.'/units/import', [
            'file' => $file,
        ], [
            'Accept' => 'application/json',
        ]);

        $response->assertOk();
        $response->assertJsonPath('summary.total_rows', 4);
        $response->assertJsonPath('summary.successful_rows', 1);
        $response->assertJsonPath('summary.failed_rows', 3);
        $response->assertJsonCount(3, 'failed_rows');

        $this->assertDatabaseHas('units', [
            'project_id' => $project->id,
            'equipment_number' => 'NEW-001',
            'category' => UnitCategory::ELEVATOR->value,
        ]);
    }

    public function test_bulk_import_returns_validation_error_when_required_headers_are_missing(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create();

        $csv = implode("\n", [
            'equipment_number,unit_type',
            'NEW-001,Company MonoSpace 700',
        ]);

        $file = UploadedFile::fake()->createWithContent('invalid-units.csv', $csv);

        $response = $this->actingAs($user)->post('/api/projects/'.$project->id.'/units/import', [
            'file' => $file,
        ], [
            'Accept' => 'application/json',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['file']);
    }
}
