<?php

namespace Tests\Unit\Services;

use App\Enums\Status;
use App\Models\Project;
use App\Models\Unit;
use App\Services\StatusUpdateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StatusUpdateServiceTest extends TestCase
{
    use RefreshDatabase;

    protected StatusUpdateService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new StatusUpdateService;
    }

    public function test_update_status_updates_status()
    {
        $project = Project::factory()->create();
        $unit = Unit::factory()->create(['project_id' => $project->id]);
        $statusUpdate = $unit->statusUpdates()->first();

        $this->assertNull($statusUpdate->status);

        $result = $this->service->updateStatus($statusUpdate, Status::SUBMITTED);

        $this->assertEquals(Status::SUBMITTED, $result->fresh()->status);
    }

    public function test_update_status_increments_revision_on_rejection()
    {
        $project = Project::factory()->create();
        $unit = Unit::factory()->create(['project_id' => $project->id]);
        $statusUpdate = $unit->statusUpdates()->first();

        // Initial revision created by seeder/factory usually 0
        $statusUpdate->revisions()->create(['revision_number' => 0, 'revision_date' => now()]);

        $this->assertEquals(1, $statusUpdate->revisions()->count());

        $this->service->updateStatus($statusUpdate, Status::REJECTED);

        $this->assertEquals(2, $statusUpdate->revisions()->count());
        $this->assertEquals(1, $statusUpdate->revisions()->reorder()->orderByDesc('revision_number')->first()->revision_number);
    }

    public function test_upload_pdf_for_approved_status()
    {
        Storage::fake('public');

        $project = Project::factory()->create();
        $unit = Unit::factory()->create(['project_id' => $project->id]);
        $statusUpdate = $unit->statusUpdates()->first();
        $this->service->updateStatus($statusUpdate, Status::APPROVED);

        $file = UploadedFile::fake()->create('approval.pdf', 100, 'application/pdf');

        $result = $this->service->uploadPdf($statusUpdate, $file);

        $this->assertCount(1, $result->approvals);
        $this->assertNotNull($result->approvals->first()->pdf_path);
        Storage::disk('public')->assertExists($result->approvals->first()->pdf_path);
    }

    public function test_upload_pdf_for_submitted_status_creates_revision()
    {
        Storage::fake('public');

        $project = Project::factory()->create();
        $unit = Unit::factory()->create(['project_id' => $project->id]);
        $statusUpdate = $unit->statusUpdates()->first();
        $this->service->updateStatus($statusUpdate, Status::SUBMITTED);

        $file = UploadedFile::fake()->create('submission.pdf', 100, 'application/pdf');

        $result = $this->service->uploadPdf($statusUpdate, $file);

        $this->assertCount(1, $result->revisions);
        $this->assertNotNull($result->revisions->first()->pdf_path);
        Storage::disk('public')->assertExists($result->revisions->first()->pdf_path);
    }

    public function test_upload_pdf_throws_exception_for_in_progress_status()
    {
        $project = Project::factory()->create();
        $unit = Unit::factory()->create(['project_id' => $project->id]);
        $statusUpdate = $unit->statusUpdates()->first();
        $this->service->updateStatus($statusUpdate, Status::IN_PROGRESS);

        $file = UploadedFile::fake()->create('progress.pdf', 100, 'application/pdf');

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $this->expectExceptionMessage('Cannot upload PDF for in-progress status updates.');

        $this->service->uploadPdf($statusUpdate, $file);
    }
}
