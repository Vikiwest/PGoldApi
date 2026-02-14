<?php

namespace App\Http\Controllers;

use App\Services\WalletService;
use App\Http\Resources\WalletResource;
use Illuminate\Http\Request;

/**
 * WalletController: Provides wallet balance information and overview
 * 
 * Endpoint:
 * - GET /api/wallet: Retrieve all balances (NGN + all crypto)
 * 
 * Response Structure:
 * - Naira wallet: NGN balance and currency
 * - Crypto wallets: Balance, formatted output, and current NGN value
 * 
 * Design Notes:
 * - Read-only endpoint (no state changes)
 * - Fetches real-time rates from CoinGecko (via WalletService)
 * - Returns calculated portfolio value in NGN
 * 
 * Performance:
 * - Single query to load relationships
 * - Rates cached for 60 seconds
 * - Lightweight response (~1-2KB)
 * 
 * @package App\Http\Controllers
 */
class WalletController extends Controller
{
    protected WalletService $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

       /**
     * @OA\Get(
     *     path="/wallet",
     *     summary="Get user wallet balances",
     *     description="Returns the current balances for NGN and crypto wallets",
     *     operationId="getWallet",
     *     tags={"Wallet"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Wallet balances retrieved",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="naira", type="object",
     *                     @OA\Property(property="balance", type="number", format="float", example=1000000),
     *                     @OA\Property(property="formatted_balance", type="string", example="₦1,000,000.00"),
     *                     @OA\Property(property="currency", type="string", example="NGN")
     *                 ),
     *                 @OA\Property(property="crypto", type="object",
     *                     @OA\Property(property="BTC", type="object",
     *                         @OA\Property(property="balance", type="number", format="float", example=0.05),
     *                         @OA\Property(property="formatted_balance", type="string", example="0.05 BTC"),
     *                         @OA\Property(property="naira_value", type="number", format="float", example=4250000)
     *                     ),
     *                     @OA\Property(property="ETH", type="object",
     *                         @OA\Property(property="balance", type="number", format="float", example=1.5),
     *                         @OA\Property(property="naira_value", type="number", format="float", example=7500000)
     *                     ),
     *                     @OA\Property(property="USDT", type="object",
     *                         @OA\Property(property="balance", type="number", format="float", example=1000),
     *                         @OA\Property(property="naira_value", type="number", format="float", example=1500000)
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function index(Request $request)
    {
        $balances = $this->walletService->getUserWallets($request->user());
        
        return response()->json([
            'data' => $balances
        ]);
    }
}