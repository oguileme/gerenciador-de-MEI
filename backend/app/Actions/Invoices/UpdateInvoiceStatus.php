<?php

declare(strict_types=1);

namespace App\Actions\Invoices;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;

/** Altera apenas o estado de acompanhamento; os dados extraídos e o XML são imutáveis. */
class UpdateInvoiceStatus
{
    public function execute(Invoice $invoice, InvoiceStatus $status): Invoice
    {
        $invoice->status = $status;
        $invoice->save();

        return $invoice;
    }
}
