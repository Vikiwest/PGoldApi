<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = User::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Configure the model to create related wallets after creation
     */
    public function configure(): static
    {
        return $this->afterCreating(function (User $user) {
            // Create NGN wallet if it doesn't exist
            if (!$user->wallet) {
                $user->wallet()->create([
                    'balance' => fake()->randomFloat(2, 10000, 1000000),
                    'currency' => 'NGN'
                ]);
            }

            // Create crypto wallets if they don't exist
            $assets = ['BTC', 'ETH', 'USDT'];
            foreach ($assets as $asset) {
                $user->cryptoWallets()->firstOrCreate(
                    ['asset' => $asset],
                    ['balance' => fake()->randomFloat(8, 0, 5)]
                );
            }
        });
    }

    /**
     * Set specific NGN wallet balance
     */
    public function withNairaBalance(float $balance): static
    {
        return $this->afterCreating(function (User $user) use ($balance) {
            $user->wallet()->updateOrCreate(
                ['user_id' => $user->id],
                ['balance' => $balance, 'currency' => 'NGN']
            );
        });
    }

    /**
     * Set specific crypto wallet balances
     */
    public function withCryptoBalances(array $balances): static
    {
        return $this->afterCreating(function (User $user) use ($balances) {
            foreach ($balances as $asset => $balance) {
                $user->cryptoWallets()->updateOrCreate(
                    ['asset' => $asset],
                    ['balance' => $balance]
                );
            }
        });
    }

    /**
     * Create a user with zero balances
     */
    public function empty(): static
    {
        return $this->afterCreating(function (User $user) {
            $user->wallet()->update(['balance' => 0]);
            
            foreach (['BTC', 'ETH', 'USDT'] as $asset) {
                $user->cryptoWallets()
                    ->where('asset', $asset)
                    ->update(['balance' => 0]);
            }
        });
    }

    /**
     * Create a wealthy user
     */
    public function wealthy(): static
    {
        return $this->afterCreating(function (User $user) {
            $user->wallet()->update(['balance' => 10000000]); // 10 million NGN
            
            $user->cryptoWallets()
                ->where('asset', 'BTC')
                ->update(['balance' => 2.5]);
                
            $user->cryptoWallets()
                ->where('asset', 'ETH')
                ->update(['balance' => 50]);
                
            $user->cryptoWallets()
                ->where('asset', 'USDT')
                ->update(['balance' => 25000]);
        });
    }

    /**
     * Create a user with specific email
     */
    public function withEmail(string $email): static
    {
        return $this->state(fn (array $attributes) => [
            'email' => $email
        ]);
    }

    /**
     * Create a user with specific name
     */
    public function withName(string $name): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => $name
        ]);
    }
}