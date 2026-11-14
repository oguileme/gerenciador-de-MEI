<?php

declare(strict_types=1);

namespace App\Domain\Quotes;

/** Resultado decimal pronto para persistência, sem qualquer conversão para float. */
final readonly class CalculatedQuote
{
    /**
     * @param  list<array{description: string, unit_price: string, quantity: string, subtotal: string}>  $items
     */
    public function __construct(
        public array $items,
        public string $totalAmount,
    ) {}
}
