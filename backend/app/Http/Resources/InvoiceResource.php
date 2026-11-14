<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Invoice */
class InvoiceResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'mei_id' => $this->mei_id,
            'type' => $this->type->value,
            'status' => $this->status->value,
            'access_key' => $this->access_key,
            'invoice_number' => $this->invoice_number,
            'customer_name' => $this->customer_name,
            'service_amount' => $this->service_amount,
            'net_amount' => $this->net_amount,
            'issue_date' => $this->issue_date?->format('Y-m-d'),
            'competence_date' => $this->competence_date?->format('Y-m-d'),
            'description' => $this->description,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
