<?php

namespace App\Services;

use App\Models\User;
use App\Models\Wallet;
use App\Models\CryptoWallet;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * WalletService: Core business logic for wallet and trading operations
 * 
 * Responsibilities:
 * - Manages wallet balance operations (debit/credit)
 * - Orchestrates cryptocurrency buy/sell transactions
 * - Enforces business rules (minimum amounts, balance checks)
 * - Coordinates with external APIs and fee calculations
 * 
 * Design Pattern: Service Layer (encapsulates business logic, promotes testability)
 * 
 * Key Features:
 * - Database transactions ensure atomicity (all-or-nothing operations)
 * - Prevents race conditions through transactional consistency
 * - Comprehensive validation before financial operations
 * 
 * @package App\Services
 */
class WalletService
{
    protected FeeService $feeService;
    protected CoinGeckoService $coinGeckoService;

    public function __construct(FeeService $feeService, CoinGeckoService $coinGeckoService)
    {
        $this->feeService = $feeService;
        $this->coinGeckoService = $coinGeckoService;
    }

    /**
     * Get user's wallet balances
     */
    public function getUserWallets(User $user): array
    {
        $nairaWallet = $user->wallet;
        $cryptoWallets = $user->cryptoWallets;
        $rates = $this->coinGeckoService->getRates(['BTC', 'ETH', 'USDT']);

        $balances = [
            'naira' => [
                'balance' => $nairaWallet->balance,
                'currency' => $nairaWallet->currency
            ],
            'crypto' => []
        ];

        foreach ($cryptoWallets as $wallet) {
            $balances['crypto'][$wallet->asset] = [
                'balance' => $wallet->balance,
                'naira_value' => $wallet->balance * ($rates[strtolower($wallet->asset)] ?? 0)
            ];
        }

        return $balances;
    }

    /**
     * Process cryptocurrency purchase transaction
     * 
     * TRANSACTION FLOW:
     * 1. Validate minimum purchase amount
     * 2. Fetch current exchange rate from CoinGecko
     * 3. Calculate fee and total debit
     * 4. Check sufficient NGN balance (amount + fee)
     * 5. Calc crypto amount received (NGN amount ÷ rate)
     * 6. Update wallets atomically (all-or-nothing)
     * 7. Create audit trail (buy + fee transactions)
     * 
     * ATOMIC OPERATION: Database transaction ensures consistency.
     * If any step fails, all wallet changes are rolled back.
     * 
     * FEE CALCULATION: 1% of trade amount
     * Example: Buy ₦100,000 → Pay ₦101,000 = ₦100,000 (trade) + ₦1,000 (fee)
     * 
     * @param User $user
     * @param string $asset Cryptocurrency symbol (BTC, ETH, USDT)
     * @param float $nairaAmount Amount in Nigerian Naira
     * @return array Transaction details with new balances
     * @throws ValidationException On validation failure
     */
    public function processBuy(User $user, string $asset, float $nairaAmount): array
    {
        // Validate minimum amount
        $minBuy = config('trading.min_buy_amount', 5000);
        if ($nairaAmount < $minBuy) {
            throw ValidationException::withMessages([
                'amount' => ["Minimum buy amount is ₦" . number_format($minBuy)]
            ]);
        }

        return DB::transaction(function () use ($user, $asset, $nairaAmount) {
            // Get current rate
            $rate = $this->coinGeckoService->getRate($asset);
            
            // Calculate fee and total
            $feeCalc = $this->feeService->calculateBuyTotal($nairaAmount);
            
            // Get NGN wallet
            $nairaWallet = $user->wallet;
            
            // Check sufficient balance
            if ($nairaWallet->balance < $feeCalc['total']) {
                throw ValidationException::withMessages([
                    'balance' => ['Insufficient NGN balance']
                ]);
            }

            // Calculate crypto amount
            $cryptoAmount = $nairaAmount / $rate;

            // Get or create crypto wallet
            $cryptoWallet = $user->cryptoWallets()
                ->where('asset', strtoupper($asset))
                ->first();

            if (!$cryptoWallet) {
                $cryptoWallet = $user->cryptoWallets()->create([
                    'asset' => strtoupper($asset),
                    'balance' => 0
                ]);
            }

            // Deduct from NGN wallet
            $nairaWallet->balance -= $feeCalc['total'];
            $nairaWallet->save();

            // Credit crypto wallet
            $cryptoWallet->balance += $cryptoAmount;
            $cryptoWallet->save();

            // Create transaction records
            $buyTransaction = Transaction::create([
                'user_id' => $user->id,
                'type' => 'buy',
                'asset' => strtoupper($asset),
                'amount' => $cryptoAmount,
                'fee' => $feeCalc['fee'],
                'rate' => $rate,
                'metadata' => [
                    'naira_amount' => $nairaAmount,
                    'total_debited' => $feeCalc['total']
                ]
            ]);

            $feeTransaction = Transaction::create([
                'user_id' => $user->id,
                'type' => 'fee',
                'asset' => 'NGN',
                'amount' => $feeCalc['fee'],
                'metadata' => [
                    'parent_transaction' => $buyTransaction->reference,
                    'description' => "Trading fee for {$asset} purchase"
                ]
            ]);

            return [
                'transaction' => $buyTransaction,
                'crypto_amount' => $cryptoAmount,
                'rate' => $rate,
                'fee' => $feeCalc['fee'],
                'new_balances' => [
                    'naira' => $nairaWallet->balance,
                    'crypto' => $cryptoWallet->balance
                ]
            ];
        });
    }

    /**
     * Process cryptocurrency sale transaction
     * 
     * TRANSACTION FLOW:
     * 1. Retrieve crypto wallet for the asset
     * 2. Validate sufficient crypto balance
     * 3. Fetch current exchange rate from CoinGecko
     * 4. Calculate equivalent NGN value (crypto amount × rate)
     * 5. Validate minimum sale threshold (₦2,000)
     * 6. Calculate fee and credit (amount - fee)
     * 7. Update wallets atomically
     * 8. Record transaction history (sell + fee transactions)
     * 
     * ATOMIC OPERATION: Ensures balance consistency
     * 
     * FEE CALCULATION: 1% of trade value (deducted from proceeds)
     * Example: Sell ₦100,000 worth → Receive ₦99,000 = ₦100,000 (value) - ₦1,000 (fee)
     * 
     * @param User $user
     * @param string $asset Cryptocurrency symbol (BTC, ETH, USDT)
     * @param float $cryptoAmount Amount in cryptocurrency units
     * @return array Transaction details with new balances
     * @throws ValidationException On insufficient balance or validation failure
     */
    public function processSell(User $user, string $asset, float $cryptoAmount): array
    {
        return DB::transaction(function () use ($user, $asset, $cryptoAmount) {
            // Get crypto wallet
            $cryptoWallet = $user->cryptoWallets()
                ->where('asset', strtoupper($asset))
                ->firstOrFail();

            // Check sufficient crypto balance
            if ($cryptoWallet->balance < $cryptoAmount) {
                throw ValidationException::withMessages([
                    'balance' => ['Insufficient crypto balance']
                ]);
            }

            // Get current rate
            $rate = $this->coinGeckoService->getRate($asset);
            
            // Calculate NGN value
            $nairaValue = $cryptoAmount * $rate;
            
            // Validate minimum amount
            $minSell = config('trading.min_sell_amount', 2000);
            if ($nairaValue < $minSell) {
                throw ValidationException::withMessages([
                    'amount' => ["Minimum sell value is ₦" . number_format($minSell)]
                ]);
            }

            // Calculate fee and credit
            $feeCalc = $this->feeService->calculateSellCredit($nairaValue);
            
            // Get NGN wallet
            $nairaWallet = $user->wallet;

            // Deduct crypto
            $cryptoWallet->balance -= $cryptoAmount;
            $cryptoWallet->save();

            // Credit NGN wallet
            $nairaWallet->balance += $feeCalc['credit'];
            $nairaWallet->save();

            // Create transaction records
            $sellTransaction = Transaction::create([
                'user_id' => $user->id,
                'type' => 'sell',
                'asset' => strtoupper($asset),
                'amount' => $cryptoAmount,
                'fee' => $feeCalc['fee'],
                'rate' => $rate,
                'metadata' => [
                    'naira_value' => $nairaValue,
                    'credit_received' => $feeCalc['credit']
                ]
            ]);

            $feeTransaction = Transaction::create([
                'user_id' => $user->id,
                'type' => 'fee',
                'asset' => 'NGN',
                'amount' => $feeCalc['fee'],
                'metadata' => [
                    'parent_transaction' => $sellTransaction->reference,
                    'description' => "Trading fee for {$asset} sale"
                ]
            ]);

            return [
                'transaction' => $sellTransaction,
                'naira_value' => $nairaValue,
                'rate' => $rate,
                'fee' => $feeCalc['fee'],
                'credit' => $feeCalc['credit'],
                'new_balances' => [
                    'naira' => $nairaWallet->balance,
                    'crypto' => $cryptoWallet->balance
                ]
            ];
        });
    }
}