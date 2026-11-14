<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\ImportInvoiceFromXml;
use App\Domain\Invoices\Exceptions\InvalidInvoiceXml;
use App\Http\Requests\Invoice\ImportInvoiceRequest;
use App\Http\Resources\InvoiceResource;
use App\Models\Mei;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

/** Lê o upload validado e entrega o conteúdo bruto à ação responsável pela importação. */
class InvoiceImportController extends Controller
{
    public function __invoke(
        ImportInvoiceRequest $request,
        Mei $mei,
        ImportInvoiceFromXml $action,
    ): JsonResponse {
        $file = $request->file('xml');
        $temporaryPath = $file?->getRealPath();
        $contents = is_string($temporaryPath) ? file_get_contents($temporaryPath) : false;

        if (! is_string($contents)) {
            throw new InvalidInvoiceXml('Não foi possível ler o XML enviado.');
        }

        return (new InvoiceResource($action->execute($mei, $contents)))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }
}
