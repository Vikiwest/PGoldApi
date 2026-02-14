<?php

// Test CoinGecko API directly
$baseUrl = 'https://api.coingecko.com/api/v3';
$assetMap = [
    'btc' => 'bitcoin',
    'eth' => 'ethereum',
    'usdt' => 'tether',
];

echo "Testing CoinGecko API connection...\n\n";

foreach ($assetMap as $symbol => $id) {
    $url = "{$baseUrl}/simple/price?ids={$id}&vs_currencies=ngn";
    echo "Testing {$symbol} ({$id})...\n";
    echo "URL: {$url}\n";
    
    $response = @file_get_contents($url);
    
    if ($response === false) {
        echo "ERROR: Could not fetch from API\n\n";
        continue;
    }
    
    $data = json_decode($response, true);
    echo "Response: " . json_encode($data) . "\n";
    
    if (isset($data[$id]['ngn'])) {
        echo "✓ Rate for {$symbol}: ₦" . number_format($data[$id]['ngn'], 0, '.', ',') . "\n\n";
    } else {
        echo "✗ No NGN rate found\n\n";
    }
}
