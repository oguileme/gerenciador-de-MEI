<?php

declare(strict_types=1);

namespace App\Actions\Quotes;

use App\Domain\Quotes\CalculateQuote;
use App\Domain\Quotes\Exceptions\QuoteNotPending;
use App\Enums\QuoteStatus;
use App\Models\Mei;
use App\Models\Quote;
use Illuminate\Database\DatabaseManager;

/** Substitui o conteúdo de um orçamento pendente e recalcula todos os totais. */
final class UpdateQuote
{
    public function __construct(
        private readonly CalculateQuote $calculator,
        private readonly DatabaseManager $database,
    ) {}

    /**
     * @param  list<array{description: string, unit_price: string, quantity: string}>  $items
     */
    public function execute(Mei $mei, string $quoteId, string $recipientName, array $items): Quote
    {
        $calculated = $this->calculator->execute($items);

        return $this->database->transaction(function () use ($mei, $quoteId, $recipientName, $calculated): Quote {
            $quote = $this->findForUpdate($mei, $quoteId);
            if ($quote->status !== QuoteStatus::Pending) {
                throw new QuoteNotPending;
            }

            $quote->update([
                'recipient_name' => $recipientName,
                'total_amount' => $calculated->totalAmount,
            ]);
            $quote->items()->delete();
            $quote->items()->createMany($calculated->items);

            return $quote->load('items');
        });
    }

    private function findForUpdate(Mei $mei, string $quoteId): Quote
    {
        /** @var Quote */
        return $mei->quotes()->whereKey($quoteId)->lockForUpdate()->firstOrFail();
    }
}
