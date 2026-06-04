<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'full_name'          => fake()->name(),
            'email'              => fake()->unique()->safeEmail(),
            'email_verified_at'  => now(),
            'password'           => bcrypt('password'),
            'remember_token'     => Str::random(10),
            'is_admin'           => false,
            'role'               => 'client',
            'status'             => 'active',
            'kyc_status'         => 'not_submitted',
            'country'            => 'AO',
            'phone_verified_at'  => now(),
            'identity_verified_at' => now(),
            'bi_number'          => fake()->unique()->numerify('###########'),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn () => ['email_verified_at' => null]);
    }

    public function admin(): static
    {
        return $this->state(fn () => ['is_admin' => true, 'role' => 'admin']);
    }

    /** Funcionário de suporte (admin de nível baixo). */
    public function support(): static
    {
        return $this->state(fn () => ['is_admin' => true, 'is_super_admin' => false, 'role' => 'admin']);
    }

    /** Admin máximo. */
    public function superAdmin(): static
    {
        return $this->state(fn () => ['is_admin' => true, 'is_super_admin' => true, 'role' => 'super_admin']);
    }

    /** Conta desactivada. */
    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }

    public function kycIncomplete(): static
    {
        return $this->state(fn () => [
            'phone_verified_at'    => null,
            'identity_verified_at' => null,
        ]);
    }
}
