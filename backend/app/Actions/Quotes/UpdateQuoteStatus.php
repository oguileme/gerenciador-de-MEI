<?php

declare(strict_types=1);

namespace App\Actions\Quotes;

use App\Domain\Quotes\Exceptions\InvalidQuoteStatusTransition;
use App\Enums\QuoteStatus;
use App\Models\Mei;
use App\Models\Quote;
use Illuminate\Database\DatabaseManager;

/** Atualiza o estado preservando a passagem explícita por pendente. */
final class UpdateQuoteStatus
{
    public function __construct(private readonly DatabaseManager $database) {}

    public function execute(Mei $mei, string $quoteId, QuoteStatus $status): Quote
    {
        return $this->database->transaction(function () use ($mei, $quoteId, $status): Quote {
            /** @var Quote $quote */
            $quote = $mei->quotes()->whereKey($quoteId)->lockForUpdate()->firstOrFail();
            if (! $quote->status->canTransitionTo($status)) {
                throw new InvalidQuoteStatusTransition;
            }

            $quote->update(['status' => $status]);

            return $quote->load('items');
        });
    }
}
