<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CryptoWallet extends Model
{
    use HasFactory;

    protected $table = 'crypto_wallets';

    protected $fillable = [
        'user_id',
        'asset',
        'balance'
    ];

    protected $casts = [
        'balance' => 'decimal:8'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}