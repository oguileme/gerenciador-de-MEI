<?php

declare(strict_types=1);

namespace App\Enums;

enum InvoiceStatus: string
{
    case Active = 'active';
    case Cancelled = 'cancelled';
    case Substituted = 'substituted';
}
