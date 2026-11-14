<?php

declare(strict_types=1);

namespace App\Enums;

/** Estados públicos do ciclo de decisão de um orçamento. */
enum QuoteStatus: string
{
    case Pending = 'pending';
    case Accepted = 'accepted';
    case Rejected = 'rejected';

    public function canTransitionTo(self $target): bool
    {
        if ($this === $target) {
            return true;
        }

        return $this === self::Pending || $target === self::Pending;
    }
}
