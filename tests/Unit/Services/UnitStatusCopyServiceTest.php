<?php

namespace Tests\Unit\Services;

use App\Enums\Status;
use App\Enums\StatusCategory;
use App\Enums\StatusRevisionCategory;
use App\Models\Project;
use App\Models\Unit;
use App\Services\UnitStatusCopyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UnitStatusCopyServiceTest extends TestCase
{
    use RefreshDatabase;

    protected UnitStatusCopyService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new UnitStatusCopyService;
    }

    public function test_copy_status_copies_all_relevant_data()
    {
        $project = Project::factory()->create();
        $sourceUnit = Unit::factory()->create(['project_id' => $project->id]);
        $targetUnit = Unit::factory()->create(['project_id' => $project->id]);

        $sourceUpdate = $sourceUnit->statusUpdates()->where('category', StatusCategory::TECH)->first();
        $sourceUpdate->update([
            'status' => Status::APPROVED,
        ]);

        // Add some approvals to source
        $sourceUpdate->approvals()->create([
            'approval_code' => 'A',
            'comment' => 'Looks good',
            'approved_at' => now(),
            'pdf_path' => 'approvals/app1.pdf',
        ]);

        // Add some revisions to source
        $sourceUpdate->revisions()->create([
            'revision_number' => 1,
            'revision_date' => now(),
            'pdf_path' => 'revisions/rev1.pdf',
            'category' => StatusRevisionCategory::SUBMITTED,
        ]);

        // Ensure target has different initial data
        $targetUpdate = $targetUnit->statusUpdates()->where('category', StatusCategory::SAMPLE)->first();
        $targetUpdate->revisions()->create([
            'revision_number' => 0,
            'revision_date' => now()->subDay(),
            'category' => StatusRevisionCategory::SUBMITTED,
        ]);

        $this->service->copyStatus(
            $targetUnit,
            StatusCategory::SAMPLE->value,
            $sourceUnit,
            StatusCategory::TECH->value
        );

        $targetUpdate->refresh();

        $this->assertEquals(Status::APPROVED, $targetUpdate->status);

        $this->assertCount(1, $targetUpdate->approvals);
        $this->assertEquals(\App\Enums\ApprovalCode::A, $targetUpdate->approvals->first()->approval_code);
        $this->assertEquals('approvals/app1.pdf', $targetUpdate->approvals->first()->pdf_path);

        $this->assertCount(1, $targetUpdate->revisions);
        $this->assertEquals(1, $targetUpdate->revisions->first()->revision_number);
        $this->assertEquals('revisions/rev1.pdf', $targetUpdate->revisions->first()->pdf_path);
    }

    public function test_copy_status_deletes_existing_target_data()
    {
        $project = Project::factory()->create();
        $sourceUnit = Unit::factory()->create(['project_id' => $project->id]);
        $targetUnit = Unit::factory()->create(['project_id' => $project->id]);

        $sourceUpdate = $sourceUnit->statusUpdates()->where('category', StatusCategory::TECH)->first();
        $targetUpdate = $targetUnit->statusUpdates()->where('category', StatusCategory::SAMPLE)->first();

        // Add some existing data to target that should be deleted
        $targetUpdate->approvals()->create([
            'approval_code' => 'B',
            'comment' => 'Old approval',
            'approved_at' => now(),
            'approved_by' => 'Old User',
        ]);
        $targetUpdate->revisions()->create([
            'revision_number' => 5,
            'revision_date' => now(),
            'category' => StatusRevisionCategory::SUBMITTED,
        ]);

        $this->service->copyStatus(
            $targetUnit,
            StatusCategory::SAMPLE->value,
            $sourceUnit,
            StatusCategory::TECH->value
        );

        $targetUpdate->refresh();

        // Should only have data from source (which is currently empty except for defaults if we didn't add any)
        $this->assertCount(0, $targetUpdate->approvals);
        $this->assertCount(0, $targetUpdate->revisions);
    }

    public function test_bulk_copy_to_units()
    {
        $project = Project::factory()->create();
        $sourceUnit = Unit::factory()->create(['project_id' => $project->id]);
        $targetUnit1 = Unit::factory()->create(['project_id' => $project->id]);
        $targetUnit2 = Unit::factory()->create(['project_id' => $project->id]);

        $sourceUpdate = $sourceUnit->statusUpdates()->where('category', StatusCategory::TECH)->first();
        $sourceUpdate->update(['status' => Status::APPROVED]);

        $this->service->bulkCopyToUnits(
            $sourceUnit,
            StatusCategory::TECH->value,
            [$targetUnit1->id, $targetUnit2->id]
        );

        $this->assertEquals(Status::APPROVED, $targetUnit1->statusUpdates()->where('category', StatusCategory::TECH)->first()->status);
        $this->assertEquals(Status::APPROVED, $targetUnit2->statusUpdates()->where('category', StatusCategory::TECH)->first()->status);
    }
}
