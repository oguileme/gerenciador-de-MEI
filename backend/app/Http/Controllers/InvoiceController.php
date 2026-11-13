<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Invoices\UpdateInvoiceStatus;
use App\Enums\InvoiceStatus;
use App\Http\Requests\Invoice\ListInvoicesRequest;
use App\Http\Requests\Invoice\UpdateInvoiceStatusRequest;
use App\Http\Resources\InvoiceResource;
use App\Models\Mei;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class InvoiceController extends Controller
{
    public function index(ListInvoicesRequest $request, Mei $mei): AnonymousResourceCollection
    {
        $query = $mei->invoices()->orderByDesc('issue_date')->orderByDesc('id');
        if ($request->filled('status')) {
            $query->where('status', $request->validated('status'));
        }

        return InvoiceResource::collection($query->paginate(10));
    }

    public function updateStatus(
        UpdateInvoiceStatusRequest $request,
        Mei $mei,
        string $invoice,
        UpdateInvoiceStatus $action,
    ): InvoiceResource {
        // A consulta pelo vínculo impede usar uma nota de outro MEI, mesmo da mesma conta.
        $record = $mei->invoices()->findOrFail($invoice);

        return new InvoiceResource($action->execute(
            $record,
            InvoiceStatus::from($request->validated('status')),
        ));
    }
}
