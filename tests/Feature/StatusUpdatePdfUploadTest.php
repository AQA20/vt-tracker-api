<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class StatusUpdatePdfUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_upload_pdf_for_approved_status()
    {
        Storage::fake('public');
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $project = Project::factory()->create();
        $unit = Unit::factory()->create(['project_id' => $project->id]);
        $statusUpdate = $unit->statusUpdates()->first();

        // Set to APPROVED
        $this->patchJson("/api/status-updates/{$statusUpdate->id}", ['status' => 'approved']);

        $file = UploadedFile::fake()->create('approval.pdf', 100, 'application/pdf');

        $response = $this->postJson("/api/status-updates/{$statusUpdate->id}/upload-pdf", [
            'pdf' => $file,
        ]);

        $response->assertStatus(200);

        $statusUpdate->refresh();
        $this->assertCount(1, $statusUpdate->approvals);
        Storage::disk('public')->assertExists($statusUpdate->approvals->first()->pdf_path);
    }

    public function test_can_upload_pdf_for_submitted_status_creates_revision()
    {
        Storage::fake('public');
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $project = Project::factory()->create();
        $unit = Unit::factory()->create(['project_id' => $project->id]);
        $statusUpdate = $unit->statusUpdates()->first();

        // Set to SUBMITTED
        $this->patchJson("/api/status-updates/{$statusUpdate->id}", ['status' => 'submitted']);

        $file = UploadedFile::fake()->create('submission.pdf', 100, 'application/pdf');

        $response = $this->postJson("/api/status-updates/{$statusUpdate->id}/upload-pdf", [
            'pdf' => $file,
        ]);

        $response->assertStatus(200);

        $statusUpdate->refresh();
        $this->assertCount(1, $statusUpdate->revisions);
        Storage::disk('public')->assertExists($statusUpdate->revisions->first()->pdf_path);
    }

    public function test_cannot_upload_pdf_for_in_progress_status()
    {
        Storage::fake('public');
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $project = Project::factory()->create();
        $unit = Unit::factory()->create(['project_id' => $project->id]);
        $statusUpdate = $unit->statusUpdates()->first();

        // Set to IN_PROGRESS
        $this->patchJson("/api/status-updates/{$statusUpdate->id}", ['status' => 'in_progress']);

        $file = UploadedFile::fake()->create('progress.pdf', 100, 'application/pdf');

        $response = $this->postJson("/api/status-updates/{$statusUpdate->id}/upload-pdf", [
            'pdf' => $file,
        ]);

        $response->assertStatus(422);
        $this->assertEquals('Cannot upload PDF for in-progress status updates.', $response->json('message'));
    }

    public function test_upload_pdf_requires_valid_pdf_file()
    {
        Storage::fake('public');
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $project = Project::factory()->create();
        $unit = Unit::factory()->create(['project_id' => $project->id]);
        $statusUpdate = $unit->statusUpdates()->first();

        $file = UploadedFile::fake()->create('invalid.txt', 100, 'text/plain');

        $response = $this->postJson("/api/status-updates/{$statusUpdate->id}/upload-pdf", [
            'pdf' => $file,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['pdf']);
    }
}
