<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Quotes\RenderQuotePdf;
use App\Models\Mei;
use App\Models\Quote;
use Illuminate\Http\Response;

/** Entrega o PDF sob demanda depois de resolver o orçamento dentro do MEI. */
class QuotePdfController extends Controller
{
    public function __invoke(Mei $mei, string $quote, RenderQuotePdf $renderer): Response
    {
        /** @var Quote $record */
        $record = $mei->quotes()->with('items')->findOrFail($quote);
        $pdf = $renderer->execute($record);

        return response($pdf, Response::HTTP_OK, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => sprintf('attachment; filename="orcamento-%s.pdf"', $record->getKey()),
        ]);
    }
}
