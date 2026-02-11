<?php

namespace Database\Seeders;

use App\Models\SupplyChainReference;
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
        // Get all existing units from ProjectSeeder
        $allUnits = Unit::all();

        if ($allUnits->isEmpty()) {
            $this->command->warn('No units found. Please run ProjectSeeder first.');

            return;
        }

        // Add delivery groups to all units
        foreach ($allUnits as $unit) {
            // Create 2-4 Delivery Groups per unit
            $groupCount = rand(2, 4);

            for ($i = 1; $i <= $groupCount; $i++) {
                $group = $this->deliveryService->createGroup($unit, "Delivery Group $i", $i);

                // Create supply chain reference for the group
                SupplyChainReference::create([
                    'delivery_group_id' => $group->id,
                    'dir_reference' => 'DIR-'.$unit->equipment_number.'-DG'.$i,
                    'csp_reference' => 'CSP-'.$unit->equipment_number.'-DG'.$i,
                    'source' => 'Europe Supply',
                    'delivery_terms' => 'CIF',
                ]);

                // Randomize milestone dates
                $this->randomizeMilestones($group);
            }
        }

        $this->command->info('Created delivery groups and milestones for '.$allUnits->count().' units.');
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
