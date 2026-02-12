<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Project>
 */
class ProjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->company.' Tower',
            'kone_project_id' => 'KP'.$this->faker->unique()->numberBetween(100000, 999999),
            'client_name' => $this->faker->company,
            'location' => $this->faker->city,
        ];
    }
}
