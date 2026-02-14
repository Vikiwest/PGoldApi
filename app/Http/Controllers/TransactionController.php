<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Http\Resources\TransactionResource;
use Illuminate\Http\Request;

/**
 * TransactionController: Provides transaction history and audit trail
 * 
 * Endpoint:
 * - GET /api/transactions: Retrieve paginated transaction history
 * 
 * Features:
 * - Filtering by transaction type (buy, sell, fee, deposit)
 * - Filtering by asset (BTC, ETH, USDT, NGN)
 * - Pagination (default 15 per page)
 * - Sorted by newest first
 * 
 * Design Notes:
 * - User can only see their own transactions (via auth middleware)
 * - Complete audit trail of all operations
 * - Supports compliance and financial reporting
 * 
 * Query Optimization:
 * - Indexed queries on (user_id, type) and (user_id, asset)
 * - Pagination prevents loading thousands of records
 * - Single query per request (no N+1 problems)
 * 
 * Example Filters:
 * - ?type=buy              → Only buy transactions
 * - ?asset=BTC            → Only BTC-related transactions
 * - ?type=fee&asset=NGN   → Only fee charges
 * - ?page=2&per_page=50   → Custom pagination
 * 
 * @package App\Http\Controllers
 */
class TransactionController extends Controller
{
     /**
     * @OA\Get(
     *     path="/transactions",
     *     summary="Get transaction history",
     *     description="Returns paginated list of user transactions with optional filters",
     *     operationId="getTransactions",
     *     tags={"Transactions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="Filter by transaction type",
     *         required=false,
     *         @OA\Schema(type="string", enum={"buy","sell","fee","deposit"})
     *     ),
     *     @OA\Parameter(
     *         name="asset",
     *         in="query",
     *         description="Filter by asset",
     *         required=false,
     *         @OA\Schema(type="string", enum={"BTC","ETH","USDT","NGN"})
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Items per page",
     *         required=false,
     *         @OA\Schema(type="integer", default=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Transactions retrieved",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="links", type="object"),
     *             @OA\Property(property="meta", type="object")
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
        $query = $request->user()->transactions();

        // Apply filters
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('asset')) {
            $query->where('asset', strtoupper($request->asset));
        }

        // Sort by latest first
        $query->latest();

        $transactions = $query->paginate(15);

        return TransactionResource::collection($transactions);
    }
}