<?php

namespace Tests\Unit\Services;

use App\Enums\MilestoneCode;
use App\Models\DeliveryGroup;
use App\Models\Project;
use App\Models\Unit;
use App\Services\DeliveryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeliveryServiceTest extends TestCase
{
    use RefreshDatabase;

    protected DeliveryService $deliveryService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->deliveryService = new DeliveryService;
    }

    public function test_create_group_creates_group_and_all_standard_milestones()
    {
        $project = Project::factory()->create();
        $unit = Unit::factory()->create(['project_id' => $project->id]);

        $groupName = 'Test Group';
        $groupNumber = 1;
        $notes = 'Test notes';

        $group = $this->deliveryService->createGroup($unit, $groupName, $groupNumber, $notes);

        $this->assertInstanceOf(DeliveryGroup::class, $group);
        $this->assertEquals($unit->id, $group->unit_id);
        $this->assertEquals($groupName, $group->group_name);
        $this->assertEquals($groupNumber, $group->group_number);
        $this->assertEquals($notes, $group->notes);

        // Verify all milestones are created
        $expectedCount = count(MilestoneCode::cases());
        $this->assertEquals($expectedCount, $group->milestones()->count());

        foreach (MilestoneCode::cases() as $code) {
            $this->assertDatabaseHas('delivery_milestones', [
                'delivery_group_id' => $group->id,
                'milestone_code' => $code->value,
                'milestone_name' => $code->label(),
            ]);
        }
    }
}
