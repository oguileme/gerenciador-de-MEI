<?php

declare(strict_types=1);

namespace App\Http\Requests\Invoice;

use App\Enums\InvoiceStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListInvoicesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'page' => ['sometimes', 'integer', 'min:1'],
            'status' => ['sometimes', Rule::enum(InvoiceStatus::class)],
        ];
    }
}
