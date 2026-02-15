<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\TradeController;
use App\Http\Controllers\TransactionController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| All routes defined here are automatically prefixed with '/api'
| 
| Structure:
| - Public routes: Authentication endpoints (no middleware)
| - Protected routes: All wallet/trading endpoints (Sanctum auth required)
| 
| Authentication Method: Token-based (Laravel Sanctum)
| - Register/Login endpoints issue long-lived tokens
| - Tokens included in Authorization header: Bearer {token}
| - Tokens are tied to a specific user/device
| 
| Response Format: JSON for all endpoints
| Error Handling: HTTP status codes + descriptive messages
| 
| API Documentation: Available at /api/documentation (Swagger)
|
*/

// PUBLIC ROUTES: No authentication required
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/rates', function () {
    $service = new \App\Services\CoinGeckoService();
    return response()->json($service->getRates(['btc', 'eth', 'usdt']));
});

// PROTECTED ROUTES: Sanctum authentication required
Route::middleware('auth:sanctum')->group(function () {
    // Authentication
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Wallet Operations (read-only)
    Route::get('/wallet', [WalletController::class, 'index']);
    
    // Trading Operations (state-changing)
    Route::post('/trade/buy', [TradeController::class, 'buy']);
    Route::post('/trade/sell', [TradeController::class, 'sell']);
    
    // Transaction History (read-only with filtering)
    Route::get('/transactions', [TransactionController::class, 'index']);
});


