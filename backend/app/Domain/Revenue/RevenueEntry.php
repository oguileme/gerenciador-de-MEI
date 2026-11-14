<?php

declare(strict_types=1);

namespace App\Domain\Revenue;

/** Dados fiscais mínimos para cálculo independente da persistência. */
final readonly class RevenueEntry
{
    public function __construct(
        public string $amount,
        public string $issueDate,
        public string $status,
    ) {}
}
