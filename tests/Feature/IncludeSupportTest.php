<?php

namespace Tests\Feature;

use App\Enums\ApprovalCode;
use App\Enums\Status;
use App\Enums\StatusCategory;
use App\Models\Project;
use App\Models\StatusApproval;
use App\Models\StatusRevision;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class IncludeSupportTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_include_nested_relations_with_whitespace()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $project = Project::factory()->create();
        $unit = Unit::factory()->create(['project_id' => $project->id]);

        $statusUpdate = $unit->statusUpdates()->where('category', StatusCategory::TECH)->first();
        $statusUpdate->update(['status' => Status::SUBMITTED]);

        StatusRevision::create([
            'status_update_id' => $statusUpdate->id,
            'revision_number' => 0,
            'pdf_path' => 'rev.pdf',
        ]);

        StatusApproval::create([
            'status_update_id' => $statusUpdate->id,
            'approval_code' => ApprovalCode::A,
            'comment' => 'Ok',
        ]);

        // Test with whitespace and multiple commas as reported by user
        $response = $this->getJson("/api/projects/{$project->id}/units?include=statusUpdates, statusUpdates.revisions, statusUpdates.approvals,,stages.tasks");

        $response->assertStatus(200);

        $unitData = $response->json('data.0');

        $this->assertArrayHasKey('status_updates', $unitData);
        $this->assertNotEmpty($unitData['status_updates']);

        $techUpdate = collect($unitData['status_updates'])->where('category', 'tech')->first();

        $this->assertArrayHasKey('revisions', $techUpdate, 'Revisions should be included');
        $this->assertNotEmpty($techUpdate['revisions'], 'Revisions should not be empty');

        $this->assertArrayHasKey('approvals', $techUpdate, 'Approvals should be included');
        $this->assertNotEmpty($techUpdate['approvals'], 'Approvals should not be empty');
    }
}
