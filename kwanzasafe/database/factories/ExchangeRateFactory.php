<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ExchangeRateFactory extends Factory
{
    public function definition(): array
    {
        return [
            'currency_from' => fake()->randomElement(['EUR', 'BRL']),
            'currency_to'   => 'AOA',
            'rate'          => 850.0000,
            'is_active'     => true,
            'created_by'    => null,
        ];
    }
}
