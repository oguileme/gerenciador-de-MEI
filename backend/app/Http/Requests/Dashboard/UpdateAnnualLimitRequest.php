<?php

declare(strict_types=1);

namespace App\Http\Requests\Dashboard;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAnnualLimitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'year' => ['required', 'integer', 'between:1900,2100'],
            'amount' => ['required', 'string', 'regex:/^(?:0|[1-9]\d{0,12})\.\d{2}$/', 'not_in:0.00'],
            'mei_id' => ['prohibited'],
        ];
    }
}
