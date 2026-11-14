<?php

declare(strict_types=1);

namespace App\Actions\Invoices;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;

/**
 * Altera apenas o estado de acompanhamento; dados extraídos e XML são imutáveis.
 *
 * A operação não cancela nem substitui a nota no emissor oficial. O status determina
 * somente se o documento participa dos cálculos internos de faturamento.
 */
class UpdateInvoiceStatus
{
    public function execute(Invoice $invoice, InvoiceStatus $status): Invoice
    {
        $invoice->status = $status;
        $invoice->save();

        return $invoice;
    }
}
