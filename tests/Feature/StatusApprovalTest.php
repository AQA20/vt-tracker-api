<?php

namespace Tests\Feature;

use App\Enums\ApprovalCode;
use App\Models\Project;
use App\Models\StatusApproval;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class StatusApprovalTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_update_status_approval_date()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $project = Project::factory()->create();
        $unit = Unit::factory()->create(['project_id' => $project->id]);
        $statusUpdate = $unit->statusUpdates->first();

        $statusApproval = StatusApproval::create([
            'status_update_id' => $statusUpdate->id,
            'approval_code' => ApprovalCode::A,
            'approved_at' => null,
        ]);

        $approvedAt = now()->startOfMinute();

        $response = $this->patchJson("/api/status-approvals/{$statusApproval->id}", [
            'approved_at' => $approvedAt->toIso8601String(),
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.approved_at', $approvedAt->toISOString());

        $this->assertEquals($approvedAt->timestamp, $statusApproval->fresh()->approved_at->timestamp);
    }

    public function test_update_status_approval_requires_valid_date()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $project = Project::factory()->create();
        $unit = Unit::factory()->create(['project_id' => $project->id]);
        $statusUpdate = $unit->statusUpdates->first();

        $statusApproval = StatusApproval::create([
            'status_update_id' => $statusUpdate->id,
            'approval_code' => ApprovalCode::A,
        ]);

        $response = $this->patchJson("/api/status-approvals/{$statusApproval->id}", [
            'approved_at' => 'not-a-date',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['approved_at']);
    }

    public function test_can_create_status_approval()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $project = Project::factory()->create();
        $unit = Unit::factory()->create(['project_id' => $project->id]);
        $statusUpdate = $unit->statusUpdates()->first();

        $response = $this->postJson('/api/status-approvals', [
            'status_update_id' => $statusUpdate->id,
            'approval_code' => ApprovalCode::A->value,
            'approved_at' => now()->toIso8601String(),
            'comment' => 'New approval',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.comment', 'New approval');

        $this->assertDatabaseHas('status_approvals', [
            'status_update_id' => $statusUpdate->id,
            'approval_code' => 'A',
        ]);
    }

    public function test_cannot_create_duplicate_status_approval()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $project = Project::factory()->create();
        $unit = Unit::factory()->create(['project_id' => $project->id]);
        $statusUpdate = $unit->statusUpdates()->first();

        // Create first approval
        StatusApproval::create([
            'status_update_id' => $statusUpdate->id,
            'approval_code' => ApprovalCode::A,
        ]);

        // Try to create another one with same update and code
        $response = $this->postJson('/api/status-approvals', [
            'status_update_id' => $statusUpdate->id,
            'approval_code' => ApprovalCode::A->value,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status_update_id']);
    }
}
