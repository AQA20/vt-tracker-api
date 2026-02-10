<?php

namespace Tests\Feature;

use App\Enums\MilestoneCode;
use App\Models\DeliveryGroup;
use App\Models\DeliveryMilestone;
use App\Models\Project;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DeliveryTrackingTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_delivery_group_with_milestones()
    {
        $project = Project::factory()->create();
        $unit = Unit::factory()->create(['project_id' => $project->id]);

        $deliveryGroup = DeliveryGroup::create([
            'unit_id' => $unit->id,
            'group_name' => 'DG 1',
            'group_number' => 1,
        ]);

        $milestone = DeliveryMilestone::create([
            'delivery_group_id' => $deliveryGroup->id,
            'milestone_code' => MilestoneCode::ONE_C,
            'milestone_name' => 'Receive Approved Drawings',
            'planned_completion_date' => now()->addDays(30),
        ]);

        $this->assertDatabaseHas('delivery_groups', ['id' => $deliveryGroup->id]);
        $this->assertDatabaseHas('delivery_milestones', ['id' => $milestone->id]);
    }

    public function test_milestone_status_calculation()
    {
        $project = Project::factory()->create();
        $unit = Unit::factory()->create(['project_id' => $project->id]);
        $deliveryGroup = DeliveryGroup::factory()->create(['unit_id' => $unit->id]);

        // Overdue milestone
        $overdue = DeliveryMilestone::create([
            'delivery_group_id' => $deliveryGroup->id,
            'milestone_code' => '1a',
            'milestone_name' => 'Test',
            'planned_completion_date' => now()->subDays(5),
            'actual_completion_date' => null,
        ]);

        $this->assertEquals('overdue', $overdue->status);

        // On-track milestone
        $onTrack = DeliveryMilestone::create([
            'delivery_group_id' => $deliveryGroup->id,
            'milestone_code' => '1b',
            'milestone_name' => 'Test 2',
            'planned_completion_date' => now()->addDays(10),
        ]);

        $this->assertEquals('on-track', $onTrack->status);

        // Completed on time
        $completed = DeliveryMilestone::create([
            'delivery_group_id' => $deliveryGroup->id,
            'milestone_code' => '1c',
            'milestone_name' => 'Test 3',
            'planned_completion_date' => now()->subDays(5),
            'actual_completion_date' => now()->subDays(6),
        ]);

        $this->assertEquals('completed-on-time', $completed->status);
    }

    public function test_api_can_get_delivery_groups()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $project = Project::factory()->create();
        $unit = Unit::factory()->create(['project_id' => $project->id]);
        $deliveryGroup = DeliveryGroup::factory()->create(['unit_id' => $unit->id]);

        $response = $this->getJson("/api/units/{$unit->id}/delivery-groups");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'group_name', 'milestones'],
                ],
            ]);
    }

    public function test_api_can_create_delivery_group()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $project = Project::factory()->create();
        $unit = Unit::factory()->create(['project_id' => $project->id]);

        $response = $this->postJson("/api/units/{$unit->id}/delivery-groups", [
            'group_name' => 'API Group',
            'group_number' => 1,
            'notes' => 'API Notes',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('message', 'Delivery group created successfully')
            ->assertJsonPath('data.group_name', 'API Group');

        $this->assertDatabaseHas('delivery_groups', [
            'unit_id' => $unit->id,
            'group_name' => 'API Group',
        ]);
    }

    public function test_api_can_get_milestones_for_group()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $project = Project::factory()->create();
        $unit = Unit::factory()->create(['project_id' => $project->id]);
        $deliveryGroup = DeliveryGroup::factory()->create(['unit_id' => $unit->id]);
        DeliveryMilestone::create([
            'delivery_group_id' => $deliveryGroup->id,
            'milestone_code' => MilestoneCode::ONE_C,
            'milestone_name' => 'Test Milestone',
        ]);

        $response = $this->getJson("/api/delivery-groups/{$deliveryGroup->id}/milestones");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'milestone_code', 'milestone_name'],
                ],
            ]);
    }

    public function test_api_can_update_milestone()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $project = Project::factory()->create();
        $unit = Unit::factory()->create(['project_id' => $project->id]);
        $deliveryGroup = DeliveryGroup::factory()->create(['unit_id' => $unit->id]);
        $milestone = DeliveryMilestone::create([
            'delivery_group_id' => $deliveryGroup->id,
            'milestone_code' => MilestoneCode::ONE_C,
            'milestone_name' => 'Test Milestone',
        ]);

        $response = $this->patchJson("/api/delivery-milestones/{$milestone->id}", [
            'actual_completion_date' => '2026-03-01',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.actual_completion_date', '2026-03-01');

        $this->assertDatabaseHas('delivery_milestones', [
            'id' => $milestone->id,
            'actual_completion_date' => '2026-03-01 00:00:00',
        ]);
    }

    public function test_api_can_update_supply_chain_reference()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $project = Project::factory()->create();
        $unit = Unit::factory()->create(['project_id' => $project->id]);

        $response = $this->patchJson("/api/units/{$unit->id}/supply-chain-reference", [
            'dir_reference' => 'DIR-123',
            'csp_reference' => 'CSP-456',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Supply chain references updated successfully');

        $this->assertDatabaseHas('supply_chain_references', [
            'unit_id' => $unit->id,
            'dir_reference' => 'DIR-123',
            'csp_reference' => 'CSP-456',
        ]);
    }
}
