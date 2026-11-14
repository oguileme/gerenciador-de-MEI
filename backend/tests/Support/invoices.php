<?php

declare(strict_types=1);

use App\Models\Invoice;
use App\Models\Mei;

function dashboardInvoice(Mei $mei, array $attributes = []): Invoice
{
    return $mei->invoices()->create(array_merge([
        'type' => 'nfse', 'status' => 'active',
        'access_key' => str_pad((string) random_int(1, 999999999), 50, '0', STR_PAD_LEFT),
        'invoice_number' => '1', 'customer_name' => 'Cliente Fictício',
        'service_amount' => '1234.56', 'net_amount' => '1200.00',
        'issue_date' => '2026-01-01', 'competence_date' => '2025-12-31',
        'description' => 'Serviço de exemplo', 'xml_path' => 'xml/ficticio.xml',
        'xml_sha256' => hash('sha256', uniqid('fixture', true)),
    ], $attributes));
}
