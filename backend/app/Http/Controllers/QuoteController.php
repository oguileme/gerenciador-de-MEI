<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Quotes\CreateQuote;
use App\Actions\Quotes\DeleteQuote;
use App\Actions\Quotes\UpdateQuote;
use App\Actions\Quotes\UpdateQuoteStatus;
use App\Enums\QuoteStatus;
use App\Http\Requests\Quote\ListQuotesRequest;
use App\Http\Requests\Quote\SaveQuoteRequest;
use App\Http\Requests\Quote\UpdateQuoteStatusRequest;
use App\Http\Resources\QuoteResource;
use App\Models\Mei;
use App\Models\Quote;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

/** Expõe o ciclo de vida do orçamento sempre dentro do MEI autorizado. */
class QuoteController extends Controller
{
    public function index(ListQuotesRequest $request, Mei $mei): AnonymousResourceCollection
    {
        $query = $mei->quotes()->orderByDesc('issue_date')->orderByDesc('id');
        if ($request->filled('status')) {
            $query->where('status', $request->validated('status'));
        }

        return QuoteResource::collection($query->paginate(15));
    }

    public function store(SaveQuoteRequest $request, Mei $mei, CreateQuote $action): JsonResponse
    {
        $data = $request->validated();
        $quote = $action->execute($mei, $data['recipient_name'], $data['items']);

        return (new QuoteResource($quote))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Mei $mei, string $quote): QuoteResource
    {
        return new QuoteResource($this->findQuote($mei, $quote));
    }

    public function update(
        SaveQuoteRequest $request,
        Mei $mei,
        string $quote,
        UpdateQuote $action,
    ): QuoteResource {
        $data = $request->validated();

        return new QuoteResource($action->execute(
            $mei,
            $quote,
            $data['recipient_name'],
            $data['items'],
        ));
    }

    public function updateStatus(
        UpdateQuoteStatusRequest $request,
        Mei $mei,
        string $quote,
        UpdateQuoteStatus $action,
    ): QuoteResource {
        return new QuoteResource($action->execute(
            $mei,
            $quote,
            QuoteStatus::from($request->validated('status')),
        ));
    }

    public function destroy(Mei $mei, string $quote, DeleteQuote $action): Response
    {
        $action->execute($mei, $quote);

        return response()->noContent();
    }

    private function findQuote(Mei $mei, string $quoteId): Quote
    {
        /** @var Quote */
        return $mei->quotes()->with('items')->findOrFail($quoteId);
    }
}
