<?php

declare(strict_types=1);

namespace App\Domain\Quotes\Exceptions;

use RuntimeException;

/** Impede alteração de conteúdo ou exclusão após a decisão do orçamento. */
final class QuoteNotPending extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Somente orçamentos pendentes podem ser alterados ou excluídos.');
    }
}
