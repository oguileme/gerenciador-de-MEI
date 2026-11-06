<?php

declare(strict_types=1);

namespace App\Http\Requests\Mei;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMeiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'mei_name' => ['sometimes', 'string', 'max:255'],
            'opening_date' => ['sometimes', 'date_format:Y-m-d'],
            'ibge_municipality_code' => ['sometimes', 'string', 'regex:/^\d{7}$/'],
            'cnpj' => ['prohibited'],
            'user_id' => ['prohibited'],
        ];
    }
}
