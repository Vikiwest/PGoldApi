<?php

use App\Http\Controllers\PriceController;

/**
 * WEB ROUTES: Public-facing pages (not API)
 * 
 * These routes serve HTML views with server-rendered content
 * Different from API routes (/api) which return JSON
 */

// Home page with live cryptocurrency rates
Route::get('/', [PriceController::class, 'home'])->name('home');


// Health check endpoint for Render
Route::get('/health', function() {
    return response()->json([
        'status' => 'healthy',
        'timestamp' => now()->toIso8601String(),
        'database' => DB::connection()->getDatabaseName() ? 'connected' : 'disconnected',
          'swagger_exists' => file_exists(storage_path('api-docs/api-docs.json')),
        'environment' => app()->environment()
    ]);
});