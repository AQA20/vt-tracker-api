<?php

namespace Tests\Feature;

use App\Enums\Status;
use App\Enums\StatusCategory;
use App\Models\Project;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProjectStatsTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_project_statistics()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $project = Project::factory()->create();

        // Unit 1
        $unit1 = Unit::factory()->create(['project_id' => $project->id]);
        $unit1->statusUpdates()->where('category', StatusCategory::TECH)->update(['status' => Status::APPROVED]);
        $unit1->statusUpdates()->where('category', StatusCategory::LAYOUT)->update(['status' => Status::IN_PROGRESS]);

        // Unit 2
        $unit2 = Unit::factory()->create(['project_id' => $project->id]);
        $unit2->statusUpdates()->where('category', StatusCategory::TECH)->update(['status' => Status::SUBMITTED]);
        $unit2->statusUpdates()->where('category', StatusCategory::LAYOUT)->update(['status' => Status::IN_PROGRESS]);

        $response = $this->getJson("/api/projects/{$project->id}/stats");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'tech' => [
                        'approved' => 50,
                        'submitted' => 50,
                        'rejected' => 0,
                        'in_progress' => 0,
                    ],
                    'layout' => [
                        'in_progress' => 100,
                        'approved' => 0,
                        'submitted' => 0,
                        'rejected' => 0,
                    ],
                    'sample' => [
                        'approved' => 0,
                        'submitted' => 0,
                        'rejected' => 0,
                        'in_progress' => 0,
                    ],
                    // ... other categories also present with 0
                ],
            ]);
    }

    public function test_project_stats_with_no_units_returns_zeroes_for_all()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $project = Project::factory()->create();

        $response = $this->getJson("/api/projects/{$project->id}/stats");

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertCount(6, $data);
        foreach ($data as $categoryStats) {
            $this->assertCount(4, $categoryStats);
            foreach ($categoryStats as $percentage) {
                $this->assertEquals(0, $percentage);
            }
        }
    }
}
