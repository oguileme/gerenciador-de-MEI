<?php

declare(strict_types=1);

namespace App\Http\Requests\Mei;

use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMeiRequest extends FormRequest
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
            'cnpj' => [
                'required',
                'string',
                'regex:/^\d{14}$/',
                Rule::unique('mei', 'cnpj')->where(
                    fn (Builder $query): Builder => $query->where('user_id', $this->user()->getKey())
                ),
            ],
            'mei_name' => ['required', 'string', 'max:255'],
            'opening_date' => ['required', 'date_format:Y-m-d'],
            'ibge_municipality_code' => ['required', 'string', 'regex:/^\d{7}$/'],
            'annual_ceiling' => ['prohibited'],
            'user_id' => ['prohibited'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'cnpj.unique' => 'Este CNPJ já está cadastrado para sua conta, inclusive entre os MEIs excluídos. Restaure o registro existente.',
        ];
    }
}
