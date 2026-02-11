<?php

namespace Database\Seeders;

use App\Models\DeliveryGroup;
use App\Models\DeliveryGroupItem;
use App\Models\DeliveryModuleContent;
use Illuminate\Database\Seeder;

class DeliveryGroupItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $groups = DeliveryGroup::all();
        $contents = DeliveryModuleContent::all();

        if ($contents->isEmpty()) {
            $this->command->warn('No delivery module contents found. Please run DeliveryCatalogSeeder first.');

            return;
        }

        if ($groups->isEmpty()) {
            $this->command->warn('No delivery groups found. Please run DeliveryDataSeeder first.');

            return;
        }

        foreach ($groups as $group) {
            // Add 1-3 random items per group
            $itemCount = rand(1, 3);
            $selectedContents = $contents->random($itemCount);

            foreach ($selectedContents as $content) {
                DeliveryGroupItem::create([
                    'delivery_group_id' => $group->id,
                    'delivery_module_content_id' => $content->id,
                    'remarks' => fake()->boolean(50) ? fake()->sentence() : null,
                    'package_type' => fake()->randomElement(['Standard Packing', 'Sea Packing', 'Bark Free Packing']),
                    'special_delivery_address' => fake()->boolean(30) ? fake()->address() : null,
                ]);
            }
        }

        $this->command->info('Seed completed: Added items to '.$groups->count().' delivery groups.');
    }
}
