<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Transaction Model: Complete audit trail for all financial operations
 * 
 * Transaction Types:
 * - 'buy':     Cryptocurrency purchase (crypto received, NGN debited)
 * - 'sell':    Cryptocurrency sale (crypto sold, NGN credited)
 * - 'fee':     Platform fee (always in NGN)
 * - 'deposit': Initial balance or admin deposit (future use)
 * 
 * Design Pattern: Event Sourcing approach
 * Every financial operation creates immutable transaction records
 * Enables complete audit trail and dispute resolution
 * 
 * Reference Field: Prevents duplicate processing (idempotency key)
 * Format: TXN_{timestamp}_{unique_id}
 * 
 * Metadata: Stores contextual information
 * - Buy: naira_amount, total_debited, rate at purchase
 * - Sell: naira_value, credit_received, rate at sale
 * - Fee: parent_transaction reference, description
 * 
 * Precision: 8 decimal places for crypto amounts, fitting crypto standards
 * 
 * @property int $id
 * @property int $user_id (FK)
 * @property string $reference (unique, idempotency key)
 * @property string $type (buy, sell, fee, deposit)
 * @property string $asset (BTC, ETH, USDT, NGN)
 * @property decimal $amount (8 decimals for crypto precision)
 * @property decimal $fee (transaction fee)
 * @property decimal $rate (exchange rate at transaction time)
 * @property string $status (default: completed)
 * @property array $metadata (JSON context data)
 * @property timestamp $created_at
 * @property timestamp $updated_at
 */
class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'reference',
        'type',
        'asset',
        'amount',
        'fee',
        'rate',
        'status',
        'metadata'
    ];

    protected $casts = [
        'amount' => 'decimal:8',
        'fee' => 'decimal:8',
        'rate' => 'decimal:8',
        'metadata' => 'array'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected static function booted()
    {
        static::creating(function ($transaction) {
            $transaction->reference = $transaction->reference ?? self::generateReference();
        });
    }

    private static function generateReference()
    {
        return 'TXN_' . time() . '_' . uniqid();
    }
}