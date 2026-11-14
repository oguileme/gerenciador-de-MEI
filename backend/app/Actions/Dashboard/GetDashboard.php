<?php

declare(strict_types=1);

namespace App\Actions\Dashboard;

use App\Domain\Revenue\CalculateRevenue;
use App\Domain\Revenue\RevenueEntry;
use App\Models\Invoice;
use App\Models\Mei;
use App\Models\MeiAnnualLimit;
use DateTimeImmutable;

/**
 * Monta a entrada do cálculo a partir das notas e configurações persistidas.
 *
 * Um limite específico do ano prevalece sobre o limite-base do MEI. As notas são
 * percorridas por cursor para que o dashboard não carregue todos os registros na memória;
 * nenhum total ou percentual calculado é salvo no banco.
 */
class GetDashboard
{
    public function __construct(private CalculateRevenue $calculator) {}

    /**
     * Retorna o contrato completo de métricas anuais usado pelo DashboardResource.
     *
     * @return array<string, mixed>
     */
    public function execute(Mei $mei, int $year, DateTimeImmutable $today): array
    {
        // A configuração anual permite alterar um exercício sem reescrever o histórico dos demais.
        $limit = MeiAnnualLimit::query()->where('mei_id', $mei->id)->where('year', $year)->first();
        $invoices = $mei->invoices()
            ->where('status', 'active')
            ->where('issue_date', '>=', "{$year}-01-01")
            ->where('issue_date', '<', ($year + 1).'-01-01')
            ->select(['service_amount', 'issue_date', 'status'])->cursor()
            ->map(fn (Invoice $invoice): RevenueEntry => new RevenueEntry(
                $invoice->service_amount,
                $invoice->issue_date->format('Y-m-d'),
                $invoice->status->value,
            ));

        return $this->calculator->calculate(
            $invoices,
            $year,
            $limit?->amount ?? $mei->annual_ceiling,
            $mei->opening_date?->format('Y-m-d'),
            $today,
            (int) config('dashboard.warning_percent'),
            (int) config('dashboard.danger_percent'),
        );
    }
}
