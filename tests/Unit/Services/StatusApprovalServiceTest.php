<?php

namespace Tests\Unit\Services;

use App\Models\StatusApproval;
use App\Services\StatusApprovalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StatusApprovalServiceTest extends TestCase
{
    use RefreshDatabase;

    protected StatusApprovalService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new StatusApprovalService;
    }

    public function test_update_updates_status_approval_record()
    {
        $project = \App\Models\Project::factory()->create();
        $unit = \App\Models\Unit::factory()->create(['project_id' => $project->id]);
        $statusUpdate = $unit->statusUpdates()->first();

        $statusApproval = \App\Models\StatusApproval::create([
            'status_update_id' => $statusUpdate->id,
            'approval_code' => \App\Enums\ApprovalCode::A,
            'approved_at' => null,
        ]);

        $approvedAt = now()->startOfMinute();
        $data = ['approved_at' => $approvedAt];

        $result = $this->service->update($statusApproval, $data);

        $this->assertInstanceOf(StatusApproval::class, $result);
        $this->assertEquals($approvedAt->timestamp, $result->fresh()->approved_at->timestamp);
    }

    public function test_create_creates_status_approval_record()
    {
        $project = \App\Models\Project::factory()->create();
        $unit = \App\Models\Unit::factory()->create(['project_id' => $project->id]);
        $statusUpdate = $unit->statusUpdates()->first();

        $data = [
            'status_update_id' => $statusUpdate->id,
            'approval_code' => \App\Enums\ApprovalCode::A,
            'approved_at' => now()->startOfMinute(),
            'comment' => 'Test comment',
        ];

        $result = $this->service->create($data);

        $this->assertInstanceOf(StatusApproval::class, $result);
        $this->assertDatabaseHas('status_approvals', [
            'status_update_id' => $statusUpdate->id,
            'approval_code' => 'A',
            'comment' => 'Test comment',
        ]);
    }
}
