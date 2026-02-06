<?php

namespace Tests\Feature;

use App\Enums\ApprovalCode;
use App\Enums\Status;
use App\Enums\StatusCategory;
use App\Models\Project;
use App\Models\StatusApproval;
use App\Models\StatusRevision;
use App\Models\StatusUpdate;
use App\Models\Unit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StatusTrackingTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_unit_automatically_creates_6_status_updates()
    {
        $project = Project::factory()->create();
        $unit = Unit::factory()->create(['project_id' => $project->id]);

        $this->assertCount(6, $unit->statusUpdates);
        foreach (StatusCategory::cases() as $category) {
            $this->assertTrue($unit->statusUpdates->contains('category', $category));
            $this->assertNull($unit->statusUpdates->where('category', $category)->first()->status);
        }
    }

    public function test_can_update_status_for_unit()
    {
        $project = Project::factory()->create();
        $unit = Unit::factory()->create(['project_id' => $project->id]);

        $statusUpdate = $unit->statusUpdates->where('category', StatusCategory::TECH)->first();
        $statusUpdate->update(['status' => Status::SUBMITTED]);

        $this->assertDatabaseHas('status_updates', [
            'id' => $statusUpdate->id,
            'category' => 'tech',
            'status' => 'submitted',
        ]);
    }

    public function test_cannot_create_duplicate_category_for_same_unit()
    {
        $project = Project::factory()->create();
        $unit = Unit::factory()->create(['project_id' => $project->id]);

        // Status updates are already created in booted()
        // Attempting to create another one for the same category should fail
        $this->expectException(\Illuminate\Database\QueryException::class);

        StatusUpdate::create([
            'unit_id' => $unit->id,
            'category' => StatusCategory::TECH,
            'status' => Status::APPROVED,
        ]);
    }

    public function test_can_add_revisions_to_status_update()
    {
        $project = Project::factory()->create();
        $unit = Unit::factory()->create(['project_id' => $project->id]);
        $statusUpdate = $unit->statusUpdates->where('category', StatusCategory::TECH)->first();

        $statusUpdate->update(['status' => Status::SUBMITTED]);

        $revision = StatusRevision::create([
            'status_update_id' => $statusUpdate->id,
            'revision_number' => 0,
            'pdf_path' => 'revisions/rev0.pdf',
            'revision_date' => now(),
        ]);

        $this->assertCount(1, $statusUpdate->refresh()->revisions);
        $this->assertEquals(0, $statusUpdate->revisions->first()->revision_number);
    }

    public function test_revision_number_must_be_between_0_and_9()
    {
        $project = Project::factory()->create();
        $unit = Unit::factory()->create(['project_id' => $project->id]);
        $statusUpdate = $unit->statusUpdates->where('category', StatusCategory::TECH)->first();

        $this->expectException(\InvalidArgumentException::class);

        StatusRevision::create([
            'status_update_id' => $statusUpdate->id,
            'revision_number' => 10,
        ]);
    }

    public function test_can_add_approvals_to_status_update()
    {
        $project = Project::factory()->create();
        $unit = Unit::factory()->create(['project_id' => $project->id]);
        $statusUpdate = $unit->statusUpdates->where('category', StatusCategory::TECH)->first();

        $statusUpdate->update(['status' => Status::APPROVED]);

        StatusApproval::create([
            'status_update_id' => $statusUpdate->id,
            'approval_code' => ApprovalCode::A,
            'pdf_path' => 'approvals/code_a.pdf',
            'approved_at' => now(),
        ]);

        $this->assertCount(1, $statusUpdate->refresh()->approvals);
        $this->assertEquals(ApprovalCode::A, $statusUpdate->approvals->first()->approval_code);
    }

    public function test_cascade_delete_works()
    {
        $project = Project::factory()->create();
        $unit = Unit::factory()->create(['project_id' => $project->id]);
        $statusUpdate = $unit->statusUpdates->where('category', StatusCategory::TECH)->first();

        StatusRevision::create([
            'status_update_id' => $statusUpdate->id,
            'revision_number' => 0,
        ]);

        StatusApproval::create([
            'status_update_id' => $statusUpdate->id,
            'approval_code' => ApprovalCode::A,
        ]);

        $statusUpdate->delete();

        $this->assertDatabaseMissing('status_updates', ['id' => $statusUpdate->id]);
        $this->assertDatabaseEmpty('status_revisions');
        $this->assertDatabaseEmpty('status_approvals');
    }
}
