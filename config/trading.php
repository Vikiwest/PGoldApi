<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Trading Configuration
    |--------------------------------------------------------------------------
    */
    
    'fee_percentage' => env('TRADE_FEE_PERCENTAGE', 1),
    
    'min_buy_amount' => env('MIN_BUY_AMOUNT', 5000),
    
    'min_sell_amount' => env('MIN_SELL_AMOUNT', 2000),
    
    'supported_assets' => ['BTC', 'ETH', 'USDT'],
];