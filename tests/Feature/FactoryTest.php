<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Wallet;
use App\Models\CryptoWallet;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FactoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_factory_creates_wallets()
    {
        $user = User::factory()->create();
        
        $this->assertNotNull($user->wallet);
        $this->assertCount(3, $user->cryptoWallets);
    }

    public function test_transaction_factory_creates_valid_data()
    {
        $transaction = Transaction::factory()->buy('BTC')->create();
        
        $this->assertEquals('buy', $transaction->type);
        $this->assertEquals('BTC', $transaction->asset);
        $this->assertEquals($transaction->amount * 0.01, $transaction->fee);
    }
}