<?php

declare(strict_types=1);

namespace App\Actions\Quotes;

use App\Domain\Quotes\CalculateQuote;
use App\Enums\QuoteStatus;
use App\Models\Mei;
use App\Models\Quote;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Carbon;

/** Cria orçamento e itens atomicamente com campos controlados pelo servidor. */
final class CreateQuote
{
    public function __construct(
        private readonly CalculateQuote $calculator,
        private readonly DatabaseManager $database,
    ) {}

    /**
     * @param  list<array{description: string, unit_price: string, quantity: string}>  $items
     */
    public function execute(Mei $mei, string $recipientName, array $items): Quote
    {
        $calculated = $this->calculator->execute($items);

        return $this->database->transaction(function () use ($mei, $recipientName, $calculated): Quote {
            $quote = $mei->quotes()->create([
                'issuer_name' => $mei->mei_name,
                'recipient_name' => $recipientName,
                'issue_date' => Carbon::now((string) config('quotes.timezone'))->toDateString(),
                'status' => QuoteStatus::Pending,
                'total_amount' => $calculated->totalAmount,
            ]);
            $quote->items()->createMany($calculated->items);

            return $quote->load('items');
        });
    }
}
