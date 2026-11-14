<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Mei;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Mei */
class MeiResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'cnpj' => $this->cnpj,
            'mei_name' => $this->mei_name,
            'opening_date' => $this->opening_date?->format('Y-m-d'),
            'ibge_municipality_code' => $this->ibge_municipality_code,
            'annual_ceiling' => $this->annual_ceiling,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
