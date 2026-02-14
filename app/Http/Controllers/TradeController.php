<?php

namespace App\Http\Controllers;

use App\Http\Requests\BuyCryptoRequest;
use App\Http\Requests\SellCryptoRequest;
use App\Services\WalletService;
use App\Http\Resources\TransactionResource;

/**
 * TradeController: HTTP endpoint handler for cryptocurrency trading
 * 
 * Responsibilities:
 * - Route HTTP requests to WalletService
 * - Validate user input via Form Requests
 * - Format responses via API Resources
 * - Handle request/response lifecycle
 * 
 * Design Pattern: Thin Controller Pattern
 * Controllers are thin (10-15 lines per method)
 * Business logic resides in WalletService
 * This separation improves testability and maintainability
 * 
 * Request Flow:
 * 1. HTTP request arrives
 * 2. Form Request validates input
 * 3. WalletService processes business logic
 * 4. TransactionResource formats response
 * 5. JSON returned to client
 * 
 * Error Handling:
 * - ValidationException from service becomes 400 response
 * - Unhandled exceptions bubble to exception handler
 * - All errors include descriptive messages
 * 
 * @package App\Http\Controllers
 */
class TradeController extends Controller
{
    protected WalletService $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

   /**
     * @OA\Post(
     *     path="/trade/buy",
     *     summary="Buy cryptocurrency",
     *     description="Purchase crypto assets using NGN wallet balance",
     *     operationId="buyCrypto",
     *     tags={"Trading"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"asset","amount"},
     *             @OA\Property(property="asset", type="string", enum={"BTC","ETH","USDT"}, example="BTC", description="Crypto asset to buy"),
     *             @OA\Property(property="amount", type="number", format="float", example=50000, description="Amount in NGN (minimum 5000)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Purchase successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Purchase successful"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="transaction", type="object"),
     *                 @OA\Property(property="crypto_amount", type="number", example=0.00058824),
     *                 @OA\Property(property="rate", type="number", example=85000000),
     *                 @OA\Property(property="fee", type="number", example=500),
     *                 @OA\Property(property="new_balances", type="object")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Insufficient balance",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Insufficient NGN balance"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function buy(BuyCryptoRequest $request)
    {
        $result = $this->walletService->processBuy(
            $request->user(),
            $request->asset,
            $request->amount
        );

        return response()->json([
            'message' => 'Purchase successful',
            'data' => [
                'transaction' => new TransactionResource($result['transaction']),
                'crypto_amount' => $result['crypto_amount'],
                'rate' => $result['rate'],
                'fee' => $result['fee'],
                'new_balances' => $result['new_balances']
            ]
        ]);
    }

      /**
     * @OA\Post(
     *     path="/trade/sell",
     *     summary="Sell cryptocurrency",
     *     description="Sell crypto assets for NGN",
     *     operationId="sellCrypto",
     *     tags={"Trading"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"asset","amount"},
     *             @OA\Property(property="asset", type="string", enum={"BTC","ETH","USDT"}, example="BTC", description="Crypto asset to sell"),
     *             @OA\Property(property="amount", type="number", format="float", example=0.5, description="Amount in crypto (minimum depends on value)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Sale successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Sale successful"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Insufficient balance",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Insufficient crypto balance"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function sell(SellCryptoRequest $request)
    {
        $result = $this->walletService->processSell(
            $request->user(),
            $request->asset,
            $request->amount
        );

        return response()->json([
            'message' => 'Sale successful',
            'data' => [
                'transaction' => new TransactionResource($result['transaction']),
                'naira_value' => $result['naira_value'],
                'rate' => $result['rate'],
                'fee' => $result['fee'],
                'credit' => $result['credit'],
                'new_balances' => $result['new_balances']
            ]
        ]);
    }
}