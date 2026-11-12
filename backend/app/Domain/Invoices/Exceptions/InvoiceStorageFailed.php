<?php

declare(strict_types=1);

namespace App\Domain\Invoices\Exceptions;

use RuntimeException;

class InvoiceStorageFailed extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Não foi possível armazenar o XML da NFS-e.');
    }
}
