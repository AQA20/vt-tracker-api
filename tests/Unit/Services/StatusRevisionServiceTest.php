<?php

namespace Tests\Unit\Services;

use App\Models\Project;
use App\Models\StatusRevision;
use App\Models\Unit;
use App\Services\StatusRevisionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StatusRevisionServiceTest extends TestCase
{
    use RefreshDatabase;

    protected StatusRevisionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new StatusRevisionService;
    }

    public function test_create_creates_status_revision_record()
    {
        $project = Project::factory()->create();
        $unit = Unit::factory()->create(['project_id' => $project->id]);
        $statusUpdate = $unit->statusUpdates()->first();

        $data = [
            'status_update_id' => $statusUpdate->id,
            'revision_number' => 1,
            'revision_date' => now()->startOfMinute(),
            'pdf_path' => 'revisions/test.pdf',
        ];

        $result = $this->service->create($data);

        $this->assertInstanceOf(StatusRevision::class, $result);
        $this->assertDatabaseHas('status_revisions', [
            'status_update_id' => $statusUpdate->id,
            'revision_number' => 1,
            'pdf_path' => 'revisions/test.pdf',
        ]);
    }

    public function test_update_updates_status_revision_record()
    {
        $project = Project::factory()->create();
        $unit = Unit::factory()->create(['project_id' => $project->id]);
        $statusUpdate = $unit->statusUpdates()->first();

        $statusRevision = StatusRevision::create([
            'status_update_id' => $statusUpdate->id,
            'revision_number' => 0,
        ]);

        $revisionDate = now()->addDay()->startOfMinute();
        $data = ['revision_date' => $revisionDate];

        $result = $this->service->update($statusRevision, $data);

        $this->assertInstanceOf(StatusRevision::class, $result);
        $this->assertEquals($revisionDate->timestamp, $result->fresh()->revision_date->timestamp);
    }
}
