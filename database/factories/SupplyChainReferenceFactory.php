<?php

namespace Database\Factories;

use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SupplyChainReference>
 */
class SupplyChainReferenceFactory extends Factory
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
            'dir_reference' => $this->faker->bothify('DIR-####'),
            'csp_reference' => $this->faker->bothify('CSP-####'),
            'source' => 'Europe Supply',
            'delivery_terms' => 'CIF',
        ];
    }
}
