<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

class AddressFactory extends Factory
{
    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'label' => 'Home',
            'line1' => fake()->streetAddress(),
            'city' => fake()->city(),
            'province' => 'Gauteng',
            'postal_code' => fake()->postcode(),
        ];
    }
}
