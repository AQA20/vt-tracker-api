<?php

namespace Database\Factories;

use App\Models\StageTemplate;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UnitStage>
 */
class UnitStageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'unit_id' => Unit::factory(),
            'stage_template_id' => StageTemplate::factory(),
            'status' => 'pending',
            'started_at' => null,
            'completed_at' => null,
        ];
    }
}
