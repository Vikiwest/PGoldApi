<?php

namespace Database\Factories;

use App\Models\CryptoWallet;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CryptoWalletFactory extends Factory
{
    protected $model = CryptoWallet::class;

    public function definition(): array
    {
        $assets = ['BTC', 'ETH', 'USDT'];
        
        return [
            'user_id' => User::factory(),
            'asset' => fake()->randomElement($assets),
            'balance' => fake()->randomFloat(8, 0, 100),
        ];
    }

    /**
     * Set specific asset
     */
    public function forAsset(string $asset): static
    {
        return $this->state(fn (array $attributes) => [
            'asset' => strtoupper($asset)
        ]);
    }

    /**
     * Set specific balance
     */
    public function withBalance(float $balance): static
    {
        return $this->state(fn (array $attributes) => [
            'balance' => $balance
        ]);
    }

    /**
     * BTC wallet
     */
    public function btc(): static
    {
        return $this->state(fn (array $attributes) => [
            'asset' => 'BTC',
            'balance' => fake()->randomFloat(8, 0.001, 10)
        ]);
    }

    /**
     * ETH wallet
     */
    public function eth(): static
    {
        return $this->state(fn (array $attributes) => [
            'asset' => 'ETH',
            'balance' => fake()->randomFloat(8, 0.1, 100)
        ]);
    }

    /**
     * USDT wallet
     */
    public function usdt(): static
    {
        return $this->state(fn (array $attributes) => [
            'asset' => 'USDT',
            'balance' => fake()->randomFloat(2, 10, 10000)
        ]);
    }
}