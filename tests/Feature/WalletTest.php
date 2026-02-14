<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class WalletTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_wallet_balance()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->getJson('/api/wallet');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'naira' => ['balance', 'currency'],
                    'crypto' => [
                        'BTC',
                        'ETH',
                        'USDT'
                    ]
                ]
            ]);
    }

    public function test_user_can_view_transaction_history()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->getJson('/api/transactions');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'reference',
                        'type',
                        'asset',
                        'amount',
                        'fee',
                        'created_at'
                    ]
                ],
                'links',
                'meta'
            ]);
    }

    public function test_user_can_filter_transactions()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->getJson('/api/transactions?type=buy&asset=BTC');

        $response->assertStatus(200);
        
        // Verify filtered results
        if (count($response->json('data')) > 0) {
            foreach ($response->json('data') as $transaction) {
                $this->assertEquals('buy', $transaction['type']);
                $this->assertEquals('BTC', $transaction['asset']);
            }
        }
    }
}