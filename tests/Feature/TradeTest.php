<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Services\CoinGeckoService;
use Mockery;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TradeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Mock CoinGecko service
        $this->mockCoinGecko();
    }

    protected function mockCoinGecko()
    {
        $mock = Mockery::mock(CoinGeckoService::class);
        $mock->shouldReceive('getRate')
            ->andReturn(85000000); // Mock BTC rate
        
        $this->app->instance(CoinGeckoService::class, $mock);
    }

    public function test_user_can_buy_crypto()
    {
        $user = User::factory()->create();
        $user->wallet()->update(['balance' => 100000]);
        
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->postJson('/api/trade/buy', [
            'asset' => 'BTC',
            'amount' => 50000
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'transaction',
                    'crypto_amount',
                    'rate',
                    'fee',
                    'new_balances'
                ]
            ]);

        // Check fee deduction (1% = 500)
        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'type' => 'buy',
            'asset' => 'BTC'
        ]);

        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'type' => 'fee',
            'amount' => 500
        ]);

        // Check wallet balance (100000 - 50000 - 500 = 49500)
        $this->assertEquals(49500, $user->wallet->fresh()->balance);
    }

    public function test_user_cannot_buy_below_minimum()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->postJson('/api/trade/buy', [
            'asset' => 'BTC',
            'amount' => 1000 // Below minimum 5000
        ]);

        $response->assertStatus(422);
    }

    public function test_user_cannot_buy_with_insufficient_balance()
    {
        $user = User::factory()->create();
        $user->wallet()->update(['balance' => 1000]);
        
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->postJson('/api/trade/buy', [
            'asset' => 'BTC',
            'amount' => 50000
        ]);

        $response->assertStatus(422);
    }

    public function test_user_can_sell_crypto()
    {
        $user = User::factory()->create();
        $user->wallet()->update(['balance' => 10000]);
        
        // Give user some BTC
        $user->cryptoWallets()->where('asset', 'BTC')->update(['balance' => 1]);

        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->postJson('/api/trade/sell', [
            'asset' => 'BTC',
            'amount' => 0.5
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'transaction',
                    'naira_value',
                    'rate',
                    'fee',
                    'credit',
                    'new_balances'
                ]
            ]);

        // Check wallet was credited (minus fee)
        $newBalance = $user->wallet->fresh()->balance;
        $this->assertGreaterThan(10000, $newBalance);
    }

    public function test_user_cannot_sell_more_than_balance()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->postJson('/api/trade/sell', [
            'asset' => 'BTC',
            'amount' => 100 // Selling more than they have
        ]);

        $response->assertStatus(422);
    }
}