<?php

namespace Tests\Feature;

use App\Enums\Status;
use App\Enums\StatusCategory;
use App\Models\Project;
use App\Models\StatusUpdate;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StatusRevisionTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected StatusUpdate $statusUpdate;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();

        $project = Project::create(['name' => 'Test Project', 'kone_project_id' => 'KP001584', 'client_name' => 'Test Client', 'location' => 'Test Location']);
        $unit = Unit::create([
            'project_id' => $project->id,
            'unit_type' => 'Test Type',
            'equipment_number' => 'TEST-001',
            'category' => 'elevator',
        ]);

        $this->statusUpdate = $unit->statusUpdates()
            ->where('category', StatusCategory::TECH)
            ->first();

        $this->statusUpdate->update(['status' => Status::SUBMITTED]);
    }

    public function test_can_update_revision_date()
    {
        $revision = $this->statusUpdate->revisions()->create([
            'revision_number' => 0,
            'revision_date' => now()->subDay(),
        ]);

        $newDate = now()->format('Y-m-d H:i:s');

        $response = $this->actingAs($this->user)
            ->patchJson("/api/status-revisions/{$revision->id}", [
                'revision_date' => $newDate,
            ]);

        $response->assertStatus(200);
        $this->assertEquals($newDate, $revision->fresh()->revision_date->format('Y-m-d H:i:s'));
    }

    public function test_rejection_no_longer_increments_revision_number()
    {
        // Create initial revision
        $this->statusUpdate->revisions()->create([
            'revision_number' => 0,
            'revision_date' => now()->subDay(),
        ]);

        // Reject the status update
        $response = $this->actingAs($this->user)
            ->patchJson("/api/status-updates/{$this->statusUpdate->id}", [
                'status' => 'rejected',
            ]);

        $response->assertStatus(200);

        // Verify NO new revision was created
        $this->assertEquals(1, $this->statusUpdate->revisions()->count());
    }

    public function test_can_create_status_revision_with_automatic_numbering()
    {
        // Create first revision (0) for category 'submitted'
        $this->statusUpdate->revisions()->create([
            'revision_number' => 0,
            'category' => 'submitted',
            'revision_date' => now()->subDay(),
        ]);

        // Create second revision via API for 'submitted' without providing revision_number
        $response = $this->actingAs($this->user)
            ->postJson('/api/status-revisions', [
                'status_update_id' => $this->statusUpdate->id,
                'category' => 'submitted',
                'revision_date' => now()->toIso8601String(),
                'pdf_path' => 'manual/rev1.pdf',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.revision_number', 1)
            ->assertJsonPath('data.category', 'submitted');

        $this->assertDatabaseHas('status_revisions', [
            'status_update_id' => $this->statusUpdate->id,
            'category' => 'submitted',
            'revision_number' => 1,
        ]);
    }

    public function test_can_create_revisions_for_different_categories_with_separate_numbering()
    {
        // Create submitted revision 0
        $this->statusUpdate->revisions()->create([
            'revision_number' => 0,
            'category' => 'submitted',
        ]);

        // Create rejected revision via API - should get number 0
        $response = $this->actingAs($this->user)
            ->postJson('/api/status-revisions', [
                'status_update_id' => $this->statusUpdate->id,
                'category' => 'rejected',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.revision_number', 0)
            ->assertJsonPath('data.category', 'rejected');
    }

    public function test_can_create_status_revision_explicitly()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/status-revisions', [
                'status_update_id' => $this->statusUpdate->id,
                'category' => 'submitted',
                'revision_number' => 5,
                'revision_date' => now()->toIso8601String(),
                'pdf_path' => 'manual/rev5.pdf',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.revision_number', 5)
            ->assertJsonPath('data.category', 'submitted');

        $this->assertDatabaseHas('status_revisions', [
            'status_update_id' => $this->statusUpdate->id,
            'category' => 'submitted',
            'revision_number' => 5,
        ]);
    }

    public function test_cannot_create_duplicate_status_revision_within_same_category()
    {
        // Create first revision
        $this->statusUpdate->revisions()->create([
            'revision_number' => 2,
            'category' => 'submitted',
        ]);

        // Try to create another one with same update, category and number
        $response = $this->actingAs($this->user)
            ->postJson('/api/status-revisions', [
                'status_update_id' => $this->statusUpdate->id,
                'category' => 'submitted',
                'revision_number' => 2,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status_update_id']);
    }

    public function test_status_update_resource_groups_revisions_by_category()
    {
        // Create 1 submitted and 1 rejected revision
        $this->statusUpdate->revisions()->create(['revision_number' => 0, 'category' => 'submitted']);
        $this->statusUpdate->revisions()->create(['revision_number' => 0, 'category' => 'rejected']);

        $response = $this->actingAs($this->user)
            ->getJson('/api/projects'); // This endpoint includes statusUpdates.revisions via IncludeSupport or similar if tested like this

        // Actually better to test via direct StatusUpdate endpoint if it exists or units with include
        $unitId = $this->statusUpdate->unit_id;
        $response = $this->actingAs($this->user)
            ->getJson("/api/units/{$unitId}?include=statusUpdates.revisions");

        $response->assertStatus(200);

        $suData = collect($response->json('data.status_updates'))->where('id', $this->statusUpdate->id)->first();

        $this->assertArrayHasKey('revisions', $suData);
        $this->assertArrayHasKey('submitted', $suData['revisions']);
        $this->assertArrayHasKey('rejected', $suData['revisions']);
        $this->assertCount(1, $suData['revisions']['submitted']);
        $this->assertCount(1, $suData['revisions']['rejected']);
    }
}
