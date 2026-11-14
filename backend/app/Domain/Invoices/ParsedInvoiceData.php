<?php

declare(strict_types=1);

namespace App\Domain\Invoices;

final readonly class ParsedInvoiceData
{
    public function __construct(
        public string $accessKey,
        public string $invoiceNumber,
        public string $issuerCnpj,
        public string $providerCnpj,
        public ?string $customerName,
        public string $serviceAmount,
        public string $netAmount,
        public string $issueDate,
        public string $competenceDate,
        public string $description,
    ) {}
}
