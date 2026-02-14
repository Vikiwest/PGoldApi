<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SellCryptoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'asset' => 'required|string|in:BTC,ETH,USDT',
            'amount' => 'required|numeric|min:0.0001'
        ];
    }
}