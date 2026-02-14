<?php

namespace Database\Factories;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'reference' => $this->generateReference(),
            'type' => fake()->randomElement(['deposit', 'buy', 'sell', 'fee']),
            'asset' => fake()->randomElement(['BTC', 'ETH', 'USDT', 'NGN']),
            'amount' => fake()->randomFloat(8, 0.001, 1000),
            'fee' => fake()->randomFloat(2, 0, 10000),
            'rate' => fake()->randomFloat(2, 1000, 90000000),
            'status' => 'completed',
            'metadata' => ['source' => 'factory'],
            'created_at' => fake()->dateTimeBetween('-6 months', 'now'),
        ];
    }

    /**
     * Generate unique reference
     */
    private function generateReference(): string
    {
        return 'TXN_' . time() . '_' . uniqid() . '_' . rand(1000, 9999);
    }

    /**
     * Configure the factory
     */
    public function configure(): static
    {
        return $this->afterMaking(function (Transaction $transaction) {
            // Ensure reference is unique
            $transaction->reference = $this->generateReference();
            
            // Calculate fee if not set (1%)
            if (!$transaction->fee && in_array($transaction->type, ['buy', 'sell'])) {
                $transaction->fee = $transaction->amount * 0.01;
            }
        });
    }

    /**
     * Buy transaction
     */
    public function buy(string $asset = null): static
    {
        $asset = $asset ?? fake()->randomElement(['BTC', 'ETH', 'USDT']);
        
        return $this->state(fn (array $attributes) => [
            'type' => 'buy',
            'asset' => $asset,
            'fee' => ($attributes['amount'] ?? 1) * 0.01,
            'metadata' => ['action' => 'purchase']
        ]);
    }

    /**
     * Sell transaction
     */
    public function sell(string $asset = null): static
    {
        $asset = $asset ?? fake()->randomElement(['BTC', 'ETH', 'USDT']);
        
        return $this->state(fn (array $attributes) => [
            'type' => 'sell',
            'asset' => $asset,
            'fee' => ($attributes['amount'] ?? 1) * 0.01,
            'metadata' => ['action' => 'sale']
        ]);
    }

    /**
     * Fee transaction
     */
    public function fee(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'fee',
            'asset' => 'NGN',
            'fee' => 0,
            'metadata' => ['type' => 'trading_fee']
        ]);
    }

    /**
     * Deposit transaction
     */
    public function deposit(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'deposit',
            'asset' => 'NGN',
            'fee' => 0,
            'metadata' => ['method' => 'bank_transfer']
        ]);
    }

    /**
     * Transaction for specific user
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id
        ]);
    }

    /**
     * Transaction with specific amount
     */
    public function withAmount(float $amount): static
    {
        return $this->state(fn (array $attributes) => [
            'amount' => $amount,
            'fee' => $amount * 0.01
        ]);
    }

    /**
     * Recent transaction
     */
    public function recent(): static
    {
        return $this->state(fn (array $attributes) => [
            'created_at' => fake()->dateTimeBetween('-24 hours', 'now')
        ]);
    }

    /**
     * Old transaction
     */
    public function old(): static
    {
        return $this->state(fn (array $attributes) => [
            'created_at' => fake()->dateTimeBetween('-1 year', '-6 months')
        ]);
    }
}