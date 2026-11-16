<?php

declare(strict_types=1);

namespace App\Domain\Quotes\Exceptions;

use RuntimeException;

/** Sinaliza que um cálculo exato não cabe na precisão monetária persistida. */
final class QuoteAmountExceeded extends RuntimeException
{
    public function __construct(public readonly string $field)
    {
        parent::__construct('O valor calculado excede o limite permitido.');
    }
}
