<?php

namespace Database\Seeders;

use App\Enums\UnitCategory;
use App\Models\Project;
use App\Models\SupplyChainReference;
// use App\Models\DeliveryGroup; // No longer used directly
// use App\Models\DeliveryMilestone; // No longer used directly
use App\Models\Unit;
use App\Services\DeliveryService;
use Illuminate\Database\Seeder;

class DeliveryDataSeeder extends Seeder
{
    public function __construct(protected DeliveryService $deliveryService) {}

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a dedicated project for Testing Delivery
        $project = Project::factory()->create([
            'name' => 'DGP Test Project',
            'client_name' => 'KONE Corporation',
            'location' => 'Helsinki, Finland',
        ]);

        // Create 5 units for this project
        $units = Unit::factory()->count(5)->create([
            'project_id' => $project->id,
            'category' => UnitCategory::ELEVATOR,
            'unit_type' => 'Passenger Elevator',
        ]);

        foreach ($units as $index => $unit) {
            // Update unit details
            $unit->update([
                'equipment_number' => 'EQ-'.($index + 100),
                'fl_unit_name' => 'L'.($index + 1),
                'sl_reference_no' => 'SL-REF-'.($index + 500),
            ]);

            // Create Supply Chain Reference
            SupplyChainReference::factory()->create([
                'unit_id' => $unit->id,
                'dir_reference' => 'DIR-TEST-'.$index,
                'csp_reference' => 'CSP-TEST-'.$index,
            ]);

            // Create 3 Delivery Groups per unit using Service
            for ($i = 1; $i <= 3; $i++) {
                $group = $this->deliveryService->createGroup($unit, "Delivery Group $i", $i);

                // Populate random dates for some milestones
                $this->randomizeMilestones($group);
            }
        }

        // Now populate ALL other units (from ProjectSeeder) that don't have delivery groups
        $otherUnits = Unit::whereDoesntHave('deliveryGroups')->get();
        foreach ($otherUnits as $unit) {
            // Create Supply Chain Reference if missing
            if (! $unit->supplyChainReference()->exists()) {
                SupplyChainReference::factory()->create(['unit_id' => $unit->id]);
            }

            // Create 1-2 Delivery Groups
            $count = rand(1, 2);
            for ($i = 1; $i <= $count; $i++) {
                $group = $this->deliveryService->createGroup($unit, "Delivery Group $i", $i);
                $this->randomizeMilestones($group);
            }
        }
    }

    private function randomizeMilestones($group)
    {
        $milestones = $group->milestones;
        // Randomly pick some milestones to have dates
        foreach ($milestones as $milestone) {
            if (rand(0, 10) > 3) { // 70% chance to have dates
                $plannedDate = now()->addDays(fake()->numberBetween(-30, 30));

                $status = fake()->randomElement(['on-track', 'overdue', 'completed-on-time', 'completed-late']);
                $actualDate = null;

                if ($status === 'completed-on-time') {
                    $actualDate = $plannedDate->copy()->subDays(2);
                } elseif ($status === 'completed-late') {
                    $actualDate = $plannedDate->copy()->addDays(5);
                }

                // If checking overdue/on-track relative to now(), we leave actualDate null
                // logic in model handles difference.

                $milestone->update([
                    'planned_completion_date' => $plannedDate,
                    'actual_completion_date' => $actualDate,
                ]);
            }
        }
    }
}
