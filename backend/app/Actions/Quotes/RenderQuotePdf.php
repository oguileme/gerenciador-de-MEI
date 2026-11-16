<?php

declare(strict_types=1);

namespace App\Actions\Quotes;

use App\Enums\QuoteStatus;
use App\Models\Quote;
use Dompdf\Dompdf;
use Dompdf\Options;

/** Renderiza a representação atual do orçamento sem persistir arquivos derivados. */
final class RenderQuotePdf
{
    public function execute(Quote $quote): string
    {
        $options = new Options;
        // O documento usa apenas texto e CSS embutidos; acesso remoto ampliaria a superfície de ataque.
        $options->set('isRemoteEnabled', false);
        $options->set('isPhpEnabled', false);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($this->html($quote), 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }

    private function html(Quote $quote): string
    {
        $rows = '';
        foreach ($quote->items as $item) {
            $rows .= sprintf(
                '<tr><td>%s</td><td class="number">%s</td><td class="number">%s</td><td class="number">%s</td></tr>',
                nl2br($this->escape($item->description)),
                $this->escape($this->formatCurrency($item->unit_price)),
                $this->escape($this->formatQuantity($item->quantity)),
                $this->escape($this->formatCurrency($item->subtotal)),
            );
        }

        return sprintf(<<<'HTML'
<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<style>
    @page { margin: 28mm 18mm 22mm; }
    body { color: #1f2937; font-family: "DejaVu Sans", sans-serif; font-size: 11px; }
    h1 { color: #111827; font-size: 22px; margin: 0 0 22px; }
    .meta { margin-bottom: 22px; width: 100%%; }
    .meta td { padding: 3px 0; vertical-align: top; }
    .label { color: #6b7280; font-weight: bold; width: 105px; }
    table.items { border-collapse: collapse; width: 100%%; }
    .items thead { display: table-header-group; }
    .items th { background: #e5e7eb; color: #111827; padding: 8px; text-align: left; }
    .items td { border-bottom: 1px solid #d1d5db; padding: 8px; vertical-align: top; }
    .items tr { page-break-inside: avoid; }
    .number { text-align: right; white-space: nowrap; }
    .total { font-size: 15px; font-weight: bold; margin-top: 18px; text-align: right; }
</style>
</head>
<body>
<h1>Orçamento #%s</h1>
<table class="meta">
    <tr><td class="label">Emitente</td><td>%s</td></tr>
    <tr><td class="label">Destinatário</td><td>%s</td></tr>
    <tr><td class="label">Data</td><td>%s</td></tr>
    <tr><td class="label">Status</td><td>%s</td></tr>
</table>
<table class="items">
    <thead><tr><th>Serviço</th><th class="number">Valor unitário</th><th class="number">Quantidade</th><th class="number">Subtotal</th></tr></thead>
    <tbody>%s</tbody>
</table>
<div class="total">Total: %s</div>
</body>
</html>
HTML,
            $this->escape((string) $quote->getKey()),
            $this->escape($quote->issuer_name),
            $this->escape($quote->recipient_name),
            $this->escape($quote->issue_date->format('d/m/Y')),
            $this->escape($this->statusLabel($quote->status)),
            $rows,
            $this->escape($this->formatCurrency($quote->total_amount)),
        );
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    private function statusLabel(QuoteStatus $status): string
    {
        return match ($status) {
            QuoteStatus::Pending => 'Pendente',
            QuoteStatus::Accepted => 'Aceito',
            QuoteStatus::Rejected => 'Negado',
        };
    }

    private function formatCurrency(string $amount): string
    {
        return 'R$ '.$this->formatDecimal(bcadd($amount, '0', 2), 2);
    }

    private function formatQuantity(string $quantity): string
    {
        $normalized = rtrim(rtrim(bcadd($quantity, '0', 3), '0'), '.');

        return str_replace('.', ',', $normalized);
    }

    private function formatDecimal(string $value, int $scale): string
    {
        [$integer, $fraction] = explode('.', $value);
        $grouped = preg_replace('/\B(?=(\d{3})+(?!\d))/', '.', $integer) ?? $integer;

        return $grouped.','.str_pad($fraction, $scale, '0');
    }
}
