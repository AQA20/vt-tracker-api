<?php

namespace Database\Factories;

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
            'delivery_milestone_id' => null, // Should be provided by seeder
            'dir_reference' => $this->faker->bothify('DIR-####'),
            'csp_reference' => $this->faker->bothify('CSP-####'),
            'source' => 'Europe Supply',
            'delivery_terms' => 'CIF',
        ];
    }
}
