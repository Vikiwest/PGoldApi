<?php

namespace App\Services;

/**
 * FeeService: Encapsulates fee calculation logic for platform transactions
 * 
 * Design Pattern: Single Responsibility Principle
 * - Manages fee percentage configuration
 * - Provides consistent fee calculation across buy/sell operations
 * - Prevents floating-point errors with rounding
 * 
 * Fee Structure: 1% on all trades (configurable via config/trading.php)
 * - Buy: Fee added to amount (user pays more in NGN)
 * - Sell: Fee subtracted from proceeds (user receives less in NGN)
 * 
 * Precision: Uses 2 decimal places for NGN amounts
 * 
 * @package App\Services
 */
class FeeService
{
    protected float $feePercentage;

    public function __construct()
    {
        $this->feePercentage = config('trading.fee_percentage', 1) / 100;
    }

    /**
     * Calculate fee for an amount
     */
    public function calculateFee(float $amount): float
    {
        return round($amount * $this->feePercentage, 2);
    }

    /**
     * Calculate total debit for buy (amount + fee)
     */
    public function calculateBuyTotal(float $amount): array
    {
        $fee = $this->calculateFee($amount);
        $total = $amount + $fee;
        
        return [
            'amount' => $amount,
            'fee' => $fee,
            'total' => $total
        ];
    }

    /**
     * Calculate credit for sell (amount - fee)
     */
    public function calculateSellCredit(float $amount): array
    {
        $fee = $this->calculateFee($amount);
        $credit = $amount - $fee;
        
        return [
            'amount' => $amount,
            'fee' => $fee,
            'credit' => $credit
        ];
    }

    /**
     * Get fee percentage
     */
    public function getFeePercentage(): float
    {
        return $this->feePercentage * 100;
    }
}