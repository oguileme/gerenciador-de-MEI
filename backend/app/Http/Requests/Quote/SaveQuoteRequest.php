<?php

declare(strict_types=1);

namespace App\Http\Requests\Quote;

use Illuminate\Foundation\Http\FormRequest;

/** Valida o conteúdo integral enviado na criação ou substituição do orçamento. */
class SaveQuoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'recipient_name' => ['required', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1', 'max:100'],
            'items.*.description' => ['required', 'string', 'max:1000'],
            'items.*.unit_price' => [
                'required',
                'string',
                'regex:/^\d{1,8}(?:\.\d{1,2})?$/',
                'not_regex:/^0+(?:\.0{1,2})?$/',
            ],
            'items.*.quantity' => [
                'required',
                'string',
                'regex:/^\d{1,3}(?:\.\d{1,3})?$/',
                'not_regex:/^0+(?:\.0{1,3})?$/',
            ],
            'id' => ['prohibited'],
            'mei_id' => ['prohibited'],
            'issuer_name' => ['prohibited'],
            'issue_date' => ['prohibited'],
            'status' => ['prohibited'],
            'total_amount' => ['prohibited'],
            'items.*.id' => ['prohibited'],
            'items.*.quote_id' => ['prohibited'],
            'items.*.subtotal' => ['prohibited'],
        ];
    }
}
