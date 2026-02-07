<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UnitDeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_delete_unit()
    {
        $user = User::factory()->create();
        $project = Project::factory()->create();
        $unit = Unit::factory()->create(['project_id' => $project->id]);

        $this->assertDatabaseHas('units', ['id' => $unit->id]);

        $response = $this->actingAs($user)->deleteJson("/api/units/{$unit->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('units', ['id' => $unit->id]);
    }

    public function test_unauthenticated_user_cannot_delete_unit()
    {
        $project = Project::factory()->create();
        $unit = Unit::factory()->create(['project_id' => $project->id]);

        $response = $this->deleteJson("/api/units/{$unit->id}");

        $response->assertStatus(401);
        $this->assertDatabaseHas('units', ['id' => $unit->id]);
    }

    public function test_returns_404_if_unit_not_found()
    {
        $user = User::factory()->create();
        $uuid = \Illuminate\Support\Str::uuid();

        $response = $this->actingAs($user)->deleteJson("/api/units/{$uuid}");

        $response->assertStatus(404);
    }
}
