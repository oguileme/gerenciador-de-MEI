<?php

declare(strict_types=1);

namespace App\Actions\Quotes;

use App\Domain\Quotes\Exceptions\QuoteNotPending;
use App\Enums\QuoteStatus;
use App\Models\Mei;
use App\Models\Quote;
use Illuminate\Database\DatabaseManager;

/** Exclui somente propostas ainda sem decisão, incluindo seus itens. */
final class DeleteQuote
{
    public function __construct(private readonly DatabaseManager $database) {}

    public function execute(Mei $mei, string $quoteId): void
    {
        $this->database->transaction(function () use ($mei, $quoteId): void {
            /** @var Quote $quote */
            $quote = $mei->quotes()->whereKey($quoteId)->lockForUpdate()->firstOrFail();
            if ($quote->status !== QuoteStatus::Pending) {
                throw new QuoteNotPending;
            }

            $quote->delete();
        });
    }
}
