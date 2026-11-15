<?php

declare(strict_types=1);

namespace App\Domain\Quotes\Exceptions;

use RuntimeException;

/** Exige reabertura antes de trocar entre decisões finais. */
final class InvalidQuoteStatusTransition extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Volte o orçamento para pendente antes de registrar outra decisão.');
    }
}
