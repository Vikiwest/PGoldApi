<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Transaction;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create main test user with specific balances
        $mainUser = User::factory()
            ->withEmail('test@example.com')
            ->withName('Test User')
            ->withNairaBalance(1000000) // 1 million NGN
            ->withCryptoBalances([
                'BTC' => 0.05,
                'ETH' => 1.5,
                'USDT' => 1000
            ])
            ->create([
                'password' => Hash::make('password123')
            ]);

        // Create 50 transactions for main user
        $this->createTransactionsForUser($mainUser, 30);

        // Create 10 random users with their own transactions
        User::factory()
            ->count(10)
            ->create()
            ->each(function ($user) {
                // Create 10-20 transactions per user
                $this->createTransactionsForUser($user, rand(10, 20));
            });

        // Create some specific scenario users
        $this->createScenarioUsers();
    }

    private function createTransactionsForUser($user, int $count): void
    {
        $types = ['buy', 'sell', 'fee', 'deposit'];
        $assets = ['BTC', 'ETH', 'USDT', 'NGN'];
        $now = now();

        for ($i = 0; $i < $count; $i++) {
            $type = $types[array_rand($types)];
            $asset = $type === 'fee' ? 'NGN' : $assets[array_rand(['BTC', 'ETH', 'USDT'])];
            
            $amount = match($asset) {
                'BTC' => fake()->randomFloat(8, 0.001, 0.5),
                'ETH' => fake()->randomFloat(8, 0.1, 10),
                'USDT' => fake()->randomFloat(2, 10, 5000),
                default => fake()->randomFloat(2, 1000, 500000)
            };
            
            $fee = $amount * 0.01; // 1% fee
            
            $rates = [
                'BTC' => 85000000,
                'ETH' => 5000000,
                'USDT' => 1500,
                'NGN' => 1
            ];

            Transaction::create([
                'user_id' => $user->id,
                'reference' => 'TXN_' . $now->copy()->subDays(rand(0, 30))->timestamp . '_' . uniqid(),
                'type' => $type,
                'asset' => $asset,
                'amount' => $amount,
                'fee' => $fee,
                'rate' => $rates[$asset] ?? 1000,
                'status' => 'completed',
                'metadata' => json_encode(['sample' => true]),
                'created_at' => $now->copy()->subDays(rand(0, 30))->subHours(rand(0, 23)),
                'updated_at' => $now
            ]);
        }
    }

    private function createScenarioUsers(): void
    {
        // Wealthy trader
        User::factory()
            ->wealthy()
            ->withEmail('wealthy@example.com')
            ->withName('Wealthy Trader')
            ->create([
                'password' => Hash::make('password123')
            ]);

        // New user with no crypto
        User::factory()
            ->empty()
            ->withEmail('new@example.com')
            ->withName('New User')
            ->withNairaBalance(50000)
            ->create([
                'password' => Hash::make('password123')
            ]);

        // Active trader (many transactions)
        $activeTrader = User::factory()
            ->withEmail('pgold@example.com')
            ->withName('Pgold Trader')
            ->withNairaBalance(2000000)
            ->withCryptoBalances([
                'BTC' => 0.8,
                'ETH' => 12,
                'USDT' => 5000
            ])
            ->create([
                'password' => Hash::make('pgoldpassword123')
            ]);

        // Create 100 transactions for active trader
        $this->createTransactionsForUser($activeTrader, 100);
    }
}