<?php

namespace Tests\Feature;

use App\Enums\Status;
use App\Models\Project;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class StatusUpdateActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_update_status_update_status()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $project = Project::factory()->create();
        $unit = Unit::factory()->create(['project_id' => $project->id]);
        $statusUpdate = $unit->statusUpdates->first();

        $this->assertNull($statusUpdate->status);

        $response = $this->patchJson("/api/status-updates/{$statusUpdate->id}", [
            'status' => Status::SUBMITTED->value,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'submitted');

        $this->assertEquals(Status::SUBMITTED, $statusUpdate->fresh()->status);
    }

    public function test_cannot_update_status_with_invalid_value()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $project = Project::factory()->create();
        $unit = Unit::factory()->create(['project_id' => $project->id]);
        $statusUpdate = $unit->statusUpdates->first();

        $response = $this->patchJson("/api/status-updates/{$statusUpdate->id}", [
            'status' => 'invalid-status',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    public function test_status_is_required_for_update()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $project = Project::factory()->create();
        $unit = Unit::factory()->create(['project_id' => $project->id]);
        $statusUpdate = $unit->statusUpdates->first();

        $response = $this->patchJson("/api/status-updates/{$statusUpdate->id}", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }
}
