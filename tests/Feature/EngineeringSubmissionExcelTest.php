<?php

namespace Tests\Feature;

use App\Models\CseDetail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EngineeringSubmissionExcelTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_export_and_import_full_dataset()
    {
        Sanctum::actingAs(User::factory()->create());

        // 1. Prepare Data
        $cse = CseDetail::create([
            'equip_n' => 12345,
            'asset_name' => 'Test Asset',
            'unit_id' => 'U001',
            'material_code' => 'M123',
            'so_no' => 100,
            'network_no' => 200,
        ]);

        $cse->statusUpdate()->create([
            'tech_sub_status' => 'In Progress',
            'sample_status' => 'Submitted',
        ]);

        $cse->dg1Milestone()->create([
            'ms2' => '2025-01-01',
            'ms3' => '2025-02-01',
            'ms2_3s' => 14,
        ]);

        // 2. Export
        $response = $this->get('/api/engineering-submissions/export');
        $response->assertStatus(200);

        $tempFile = $response->getFile()->getPathname();

        // 3. Clear Database
        CseDetail::query()->delete();
        $this->assertDatabaseEmpty('cse_details');

        // 4. Import
        $file = new UploadedFile(
            $tempFile,
            'export.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );

        $importResponse = $this->postJson('/api/engineering-submissions/import', [
            'file' => $file,
        ]);

        $importResponse->assertStatus(200);

        // 5. Verify Data
        $this->assertDatabaseHas('cse_details', [
            'equip_n' => 12345,
            'asset_name' => 'Test Asset',
            'unit_id' => 'U001',
            'so_no' => 100,
            'network_no' => 200,
        ]);

        $this->assertDatabaseHas('status_updates', [
            'tech_sub_status' => 'In Progress',
            'sample_status' => 'Submitted',
        ]);

        $this->assertDatabaseHas('dg1_milestones', [
            'ms2' => '2025-01-01 00:00:00',
            'ms3' => '2025-02-01 00:00:00',
            'ms2_3s' => 14,
        ]);
    }
}
