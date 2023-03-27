<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CadastralParcel>
 */
class CadastralParcelFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => $this->faker->word,
            'municipality' => $this->faker->word,
            'estimated_value' => $this->faker->randomFloat(),
            'average_slope' => $this->faker->randomFloat(),
            'meter_min_distance_road' => $this->faker->randomNumber(),
            'meter_min_distance_path' => $this->faker->randomNumber(),
            'square_meter_surface' => $this->faker->randomFloat(),
            'slope' => $this->faker->randomNumber(),
            'way' => $this->faker->randomNumber(),
            'catalog_estimate' => $this->faker->word,
            'geometry' => $this->faker->word,
            'sisteco_legacy_id' => $this->faker->randomNumber(),

        ];
    }
}
