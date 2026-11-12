<?php

declare(strict_types=1);

namespace App\Domain\Invoices;

interface InvoiceXmlParser
{
    public function parse(string $xml): ParsedInvoiceData;
}
