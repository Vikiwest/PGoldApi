<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * User Model: Represents platform users with wallet relationships
 * 
 * Features:
 * - API Token Authentication (Laravel Sanctum)
 * - Automatic wallet creation on user creation
 * - Relationships to NGN wallet, crypto wallets, and transactions
 * 
 * Lifecycle:
 * - On creation: Automatically creates NGN wallet + 3 crypto wallets (BTC, ETH, USDT)
 * - Initial balances seeded for testing
 * 
 * Security:
 * - Passwords are automatically hashed via 'hashed' cast
 * - API tokens are portable across devices
 * 
 * @property int $id
 * @property string $name
 * @property string $email (unique)
 * @property string $password (hashed)
 * @property timestamp $email_verified_at
 * @property timestamp $created_at
 * @property timestamp $updated_at
 * @property Wallet $wallet Eager load via relation
 * @property Collection $cryptoWallets
 * @property Collection $transactions
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }

    public function cryptoWallets()
    {
        return $this->hasMany(CryptoWallet::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    protected static function booted()
    {
        static::created(function ($user) {
            // Create NGN wallet
            $user->wallet()->create(['balance' => 100000]); // Initial balance for testing
            
            // Create crypto wallets for supported assets
            $assets = ['BTC', 'ETH', 'USDT'];
            foreach ($assets as $asset) {
                $user->cryptoWallets()->create([
                    'asset' => $asset,
                    'balance' => 0
                ]);
            }
        });
    }
}