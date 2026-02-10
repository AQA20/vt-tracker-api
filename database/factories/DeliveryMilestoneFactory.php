<?php

namespace Database\Factories;

use App\Enums\MilestoneCode;
use App\Models\DeliveryGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DeliveryMilestone>
 */
class DeliveryMilestoneFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'delivery_group_id' => DeliveryGroup::factory(),
            'milestone_code' => MilestoneCode::ONE_A,
            'milestone_name' => 'Test Milestone',
            'planned_leadtime_days' => 10,
            'planned_completion_date' => now()->addDays(10),
            'actual_completion_date' => null,
        ];
    }
}
