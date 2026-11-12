<?php

declare(strict_types=1);

namespace App\Domain\Invoices\Exceptions;

use RuntimeException;

class DuplicateInvoice extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Esta NFS-e já foi importada para este MEI.');
    }
}
