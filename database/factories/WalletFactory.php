<?php

namespace Database\Factories;

use App\Models\Wallet;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class WalletFactory extends Factory
{
    protected $model = Wallet::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'balance' => fake()->randomFloat(2, 0, 10000000),
            'currency' => 'NGN',
        ];
    }

    /**
     * Set a specific balance
     */
    public function withBalance(float $balance): static
    {
        return $this->state(fn (array $attributes) => [
            'balance' => $balance
        ]);
    }

    /**
     * Create an empty wallet
     */
    public function empty(): static
    {
        return $this->state(fn (array $attributes) => [
            'balance' => 0
        ]);
    }

    /**
     * Create a wealthy wallet
     */
    public function wealthy(): static
    {
        return $this->state(fn (array $attributes) => [
            'balance' => fake()->randomFloat(2, 1000000, 100000000)
        ]);
    }
}