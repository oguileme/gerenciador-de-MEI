<?php

declare(strict_types=1);

namespace App\Actions\Dashboard;

use App\Domain\Revenue\CalculateRevenue;
use App\Domain\Revenue\RevenueEntry;
use App\Models\Invoice;
use App\Models\Mei;
use App\Models\MeiAnnualLimit;
use DateTimeImmutable;

/** Consulta fontes brutas e configurações; não persiste nenhum agregado. */
class GetDashboard
{
    public function __construct(private CalculateRevenue $calculator) {}

    /** @return array<string, mixed> */
    public function execute(Mei $mei, int $year, DateTimeImmutable $today): array
    {
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
            $limit?->amount ?? (string) config('dashboard.default_annual_ceiling'),
            $mei->opening_date?->format('Y-m-d'),
            $today,
            (int) config('dashboard.warning_percent'),
            (int) config('dashboard.danger_percent'),
        );
    }
}
