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