<?php

namespace App\Http\Controllers;

use App\Services\CoinGeckoService;

/**
 * PriceController: Displays cryptocurrency rates on public pages
 * 
 * Responsibilities:
 * - Fetch real-time rates from CoinGecko via service
 * - Format rates for display (NGN, formatted numbers)
 * - Provide fallback values on API failures
 * 
 * Design Pattern: Thin Controller (business logic in service)
 * 
 * Performance:
 * - Uses Service to benefit from caching (60-second TTL)
 * - Single query for all rates
 * - Graceful fallback to cached/fallback rates
 * 
 * @package App\Http\Controllers
 */
class PriceController extends Controller
{
    protected CoinGeckoService $coinGeckoService;

    public function __construct(CoinGeckoService $coinGeckoService)
    {
        $this->coinGeckoService = $coinGeckoService;
    }

    /**
     * Display home page with live cryptocurrency rates
     * 
     * Fetches current rates for:
     * - BTC (Bitcoin)
     * - ETH (Ethereum)
     * - USDT (Tether)
     * 
     * All rates displayed in Nigerian Naira (NGN)
     * Formatted with thousand separators for readability
     * 
     * @return \Illuminate\View\View
     */
    public function home()
    {
        // Fetch rates from CoinGecko (60-second cache via service)
        // Pass lowercase asset codes as the service expects them
        $rates = $this->coinGeckoService->getRates(['btc', 'eth', 'usdt']);

        // Format rates for display (NGN with thousand separators)
        $formattedRates = [
            'BTC' => [
                'rate' => $rates['btc'] ?? 85000000,
                'formatted' => number_format($rates['btc'] ?? 85000000, 0, '.', ','),
                'symbol' => '₦',
                'name' => 'Bitcoin',
                'isLive' => ($rates['btc'] ?? null) !== null
            ],
            'ETH' => [
                'rate' => $rates['eth'] ?? 5000000,
                'formatted' => number_format($rates['eth'] ?? 5000000, 0, '.', ','),
                'symbol' => '₦',
                'name' => 'Ethereum',
                'isLive' => ($rates['eth'] ?? null) !== null
            ],
            'USDT' => [
                'rate' => $rates['usdt'] ?? 1500,
                'formatted' => number_format($rates['usdt'] ?? 1500, 0, '.', ','),
                'symbol' => '₦',
                'name' => 'Tether (USDT)',
                'isLive' => ($rates['usdt'] ?? null) !== null
            ]
        ];

        return view('welcome', ['rates' => $formattedRates]);
    }
}
