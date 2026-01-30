<?php

namespace Database\Factories;

use App\Enums\UnitCategory;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Unit>
 */
class UnitFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'unit_type' => 'KONE MonoSpace 700',
            'equipment_number' => $this->faker->unique()->bothify('SN-#####'),
            'category' => UnitCategory::ELEVATOR,
            'progress_percent' => 0,
        ];
    }
}
