<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TransactionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'reference_id'   => 'KZ' . strtoupper(Str::random(6)),
            'user_id'        => User::factory(),
            'currency_from'  => fake()->randomElement(['EUR', 'BRL']),
            'currency_to'    => 'AOA',
            'amount_sent'    => fake()->randomFloat(2, 50, 5000),
            'rate_applied'   => 850.00,
            'amount_received' => fn (array $a) => round($a['amount_sent'] * $a['rate_applied'], 2),
            'fee_amount'     => 0,
            'status'         => 'pending',
        ];
    }

    public function pending(): static
    {
        return $this->state(fn () => ['status' => 'pending']);
    }

    public function negotiating(): static
    {
        return $this->state(fn () => ['status' => 'negotiating']);
    }

    public function awaitingPayment(): static
    {
        return $this->state(fn () => ['status' => 'awaiting_payment']);
    }

    public function paymentReceived(): static
    {
        return $this->state(fn () => ['status' => 'payment_received', 'payment_received_at' => now()]);
    }

    public function aoaSent(): static
    {
        return $this->state(fn () => ['status' => 'aoa_sent', 'payment_received_at' => now(), 'aoa_sent_at' => now()]);
    }

    public function completed(): static
    {
        return $this->state(fn () => [
            'status'              => 'completed',
            'payment_received_at' => now()->subMinutes(30),
            'aoa_sent_at'         => now()->subMinutes(15),
            'client_confirmed_at' => now(),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn () => ['status' => 'cancelled']);
    }
}
