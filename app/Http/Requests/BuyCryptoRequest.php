<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BuyCryptoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'asset' => 'required|string|in:BTC,ETH,USDT',
            'amount' => 'required|numeric|min:' . config('trading.min_buy_amount', 5000)
        ];
    }

    public function messages(): array
    {
        return [
            'amount.min' => 'Minimum buy amount is ₦' . number_format(config('trading.min_buy_amount', 5000))
        ];
    }
}