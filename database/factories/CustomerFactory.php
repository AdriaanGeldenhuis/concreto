<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->state(['role' => 'customer']),
            'type' => 'COD',
            'pay_before_dispatch' => false,
        ];
    }

    public function account(): static
    {
        return $this->state(['type' => 'ACCOUNT']);
    }
}
