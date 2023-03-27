<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Owner>
 */
class OwnerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sisteco_legacy_id' => $this->faker->randomNumber(),
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'email' => $this->faker->unique()->safeEmail(),
            'business_name' => $this->faker->company(),
            'vat_number' => $this->faker->vat(),
            'fiscal_code' => $this->faker->taxId(),
            'phone' => $this->faker->phoneNumber(),
            'addr:street' => $this->faker->streetName(),
            'addr:housenumber' => $this->faker->buildingNumber(),
            'addr:city' => $this->faker->city(),
            'addr:postcode' => $this->faker->postcode(),
            'addr:locality' => $this->faker->state(),
        ];
    }
}
