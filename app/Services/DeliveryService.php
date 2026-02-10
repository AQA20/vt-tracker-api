<?php

namespace App\Services;

use App\Enums\MilestoneCode;
use App\Models\DeliveryGroup;
use App\Models\DeliveryMilestone;
use App\Models\Unit;
use Illuminate\Support\Facades\DB;

class DeliveryService
{
    /**
     * Create a new delivery group and initialize all milestones.
     */
    public function createGroup(Unit $unit, string $name, int $number, ?string $notes = null): DeliveryGroup
    {
        return DB::transaction(function () use ($unit, $name, $number, $notes) {
            $group = DeliveryGroup::create([
                'unit_id' => $unit->id,
                'group_name' => $name,
                'group_number' => $number,
                'notes' => $notes,
            ]);

            // Initialize all milestones
            foreach (MilestoneCode::cases() as $code) {
                DeliveryMilestone::create([
                    'delivery_group_id' => $group->id,
                    'milestone_code' => $code,
                    'milestone_name' => $code->label(),
                    'planned_completion_date' => null,
                    'actual_completion_date' => null,
                ]);
            }

            return $group;
        });
    }
}
