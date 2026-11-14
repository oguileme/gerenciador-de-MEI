<?php

declare(strict_types=1);

namespace App\Http\Requests\Invoice;

use App\Enums\InvoiceStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateInvoiceStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(InvoiceStatus::class)],
            'mei_id' => ['prohibited'],
            'type' => ['prohibited'],
            'access_key' => ['prohibited'],
            'invoice_number' => ['prohibited'],
            'customer_name' => ['prohibited'],
            'service_amount' => ['prohibited'],
            'net_amount' => ['prohibited'],
            'issue_date' => ['prohibited'],
            'competence_date' => ['prohibited'],
            'description' => ['prohibited'],
            'xml_path' => ['prohibited'],
            'xml_sha256' => ['prohibited'],
        ];
    }
}
