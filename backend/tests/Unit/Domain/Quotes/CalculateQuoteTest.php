<?php

declare(strict_types=1);

use App\Domain\Quotes\CalculateQuote;

it('calcula subtotais e total com precisão decimal e arredondamento comercial', function (): void {
    $result = (new CalculateQuote)->execute([
        ['description' => 'Consultoria', 'unit_price' => '100.00', 'quantity' => '1.5'],
        ['description' => 'Ajuste', 'unit_price' => '0.01', 'quantity' => '0.5'],
    ]);

    expect($result->items)->toBe([
        ['description' => 'Consultoria', 'unit_price' => '100.00', 'quantity' => '1.500', 'subtotal' => '150.00'],
        ['description' => 'Ajuste', 'unit_price' => '0.01', 'quantity' => '0.500', 'subtotal' => '0.01'],
    ])->and($result->totalAmount)->toBe('150.01');
});
