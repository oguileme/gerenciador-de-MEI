<?php

declare(strict_types=1);

namespace App\Http\Requests\Invoice;

use Illuminate\Foundation\Http\FormRequest;

class ImportInvoiceRequest extends FormRequest
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
            'xml' => ['required', 'file', 'max:'.config('invoices.xml_max_kilobytes')],
            'mei_id' => ['prohibited'],
            'type' => ['prohibited'],
            'status' => ['prohibited'],
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

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'xml.max' => 'O XML não pode ser maior que :max KiB.',
        ];
    }
}
