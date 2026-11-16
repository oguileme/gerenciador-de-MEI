<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Quote;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** Contrato público do orçamento; valores decimais são sempre strings. @mixin Quote */
class QuoteResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'mei_id' => $this->mei_id,
            'issuer_name' => $this->issuer_name,
            'recipient_name' => $this->recipient_name,
            'issue_date' => $this->issue_date?->format('Y-m-d'),
            'status' => $this->status->value,
            'total_amount' => $this->total_amount,
            'items' => QuoteItemResource::collection($this->whenLoaded('items')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
