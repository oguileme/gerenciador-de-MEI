<?php

declare(strict_types=1);

namespace App\Domain\Quotes;

use App\Domain\Quotes\Exceptions\QuoteAmountExceeded;

/** Calcula subtotais e total usando aritmética decimal exata. */
final class CalculateQuote
{
    private const MAXIMUM_AMOUNT = '9999999999999.99';

    /**
     * @param  list<array{description: string, unit_price: string, quantity: string}>  $items
     *
     * @throws QuoteAmountExceeded quando um subtotal ou total excede numeric(15,2)
     */
    public function execute(array $items): CalculatedQuote
    {
        $total = '0.00';
        $calculatedItems = [];

        foreach ($items as $index => $item) {
            // Entradas são positivas; somar meio centavo antes de truncar equivale a HALF_UP.
            $exactSubtotal = bcmul($item['unit_price'], $item['quantity'], 5);
            $subtotal = bcadd($exactSubtotal, '0.005', 2);

            $this->ensureAmountFits($subtotal, "items.{$index}.unit_price");
            $total = bcadd($total, $subtotal, 2);
            $calculatedItems[] = [
                'description' => $item['description'],
                'unit_price' => bcadd($item['unit_price'], '0', 2),
                'quantity' => bcadd($item['quantity'], '0', 3),
                'subtotal' => $subtotal,
            ];
        }

        $this->ensureAmountFits($total, 'items');

        return new CalculatedQuote($calculatedItems, $total);
    }

    private function ensureAmountFits(string $amount, string $field): void
    {
        if (bccomp($amount, self::MAXIMUM_AMOUNT, 2) === 1) {
            throw new QuoteAmountExceeded($field);
        }
    }
}
