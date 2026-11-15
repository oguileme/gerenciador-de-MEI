<?php

declare(strict_types=1);

namespace App\Http\Requests\Quote;

use App\Enums\QuoteStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListQuotesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'status' => ['sometimes', Rule::enum(QuoteStatus::class)],
        ];
    }
}
