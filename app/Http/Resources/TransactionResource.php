<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reference' => $this->reference,
            'type' => $this->type,
            'asset' => $this->asset,
            'amount' => (float) $this->amount,
            'fee' => (float) $this->fee,
            'rate' => (float) $this->rate,
            'status' => $this->status,
            'metadata' => $this->metadata,
            'created_at' => $this->created_at,
            'created_at_human' => $this->created_at->diffForHumans()
        ];
    }
}