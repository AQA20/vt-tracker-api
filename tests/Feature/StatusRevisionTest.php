<?php

namespace Tests\Feature;

use App\Enums\Status;
use App\Enums\StatusCategory;
use App\Models\Project;
use App\Models\StatusRevision;
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

        $project = Project::create(['name' => 'Test Project', 'client_name' => 'Test Client', 'location' => 'Test Location']);
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

    public function test_increment_revision_number_on_rejection()
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

        // Verify new revision was created with incremented number
        $this->assertEquals(2, $this->statusUpdate->revisions()->count());
        $latestRevision = $this->statusUpdate->revisions()->reorder()->orderByDesc('revision_number')->first();
        $this->assertEquals(1, $latestRevision->revision_number);
    }

    public function test_create_initial_revision_on_rejection_if_none_exists()
    {
        // Reject without any existing revisions
        $response = $this->actingAs($this->user)
            ->patchJson("/api/status-updates/{$this->statusUpdate->id}", [
                'status' => 'rejected',
            ]);

        $response->assertStatus(200);

        $this->assertEquals(1, $this->statusUpdate->revisions()->count());
        $this->assertEquals(1, $this->statusUpdate->revisions()->first()->revision_number);
    }

    public function test_can_create_status_revision()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/status-revisions', [
                'status_update_id' => $this->statusUpdate->id,
                'revision_number' => 5,
                'revision_date' => now()->toIso8601String(),
                'pdf_path' => 'manual/rev5.pdf',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.revision_number', 5);

        $this->assertDatabaseHas('status_revisions', [
            'status_update_id' => $this->statusUpdate->id,
            'revision_number' => 5,
        ]);
    }

    public function test_cannot_create_duplicate_status_revision()
    {
        // Create first revision
        StatusRevision::create([
            'status_update_id' => $this->statusUpdate->id,
            'revision_number' => 2,
        ]);

        // Try to create another one with same update and number
        $response = $this->actingAs($this->user)
            ->postJson('/api/status-revisions', [
                'status_update_id' => $this->statusUpdate->id,
                'revision_number' => 2,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status_update_id']);
    }
}
