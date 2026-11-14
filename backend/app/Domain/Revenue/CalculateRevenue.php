<?php

declare(strict_types=1);

namespace App\Domain\Revenue;

use DateTimeImmutable;

/** Calcula sob demanda por emissão, preservando centavos e excluindo notas não ativas. */
final class CalculateRevenue
{
    private const MONTHS_PER_YEAR = 12;

    /** @param iterable<RevenueEntry> $entries
     * @return array<string, mixed>
     */
    public function calculate(
        iterable $entries,
        int $year,
        string $ceiling,
        ?string $openingDate,
        DateTimeImmutable $today,
        int $warningPercent,
        int $dangerPercent,
    ): array {
        $months = array_fill(1, self::MONTHS_PER_YEAR, '0.00');
        $count = 0;
        foreach ($entries as $entry) {
            if ($entry->status !== 'active' || (int) substr($entry->issueDate, 0, 4) !== $year) {
                continue;
            }
            $month = (int) substr($entry->issueDate, 5, 2);
            $months[$month] = bcadd($months[$month], $entry->amount, 2);
            $count++;
        }
        $annual = $this->sum($months);
        $limit = $ceiling;
        $percent = $this->divideRounded(bcmul($annual, '100', 2), $limit);
        // As faixas comparam valores exatos, antes do arredondamento de exibição do percentual.
        $state = bccomp(bcmul($annual, '100', 2), bcmul($limit, (string) $dangerPercent, 2), 2) >= 0
            ? 'danger'
            : (bccomp(bcmul($annual, '100', 2), bcmul($limit, (string) $warningPercent, 2), 2) >= 0 ? 'warning' : 'safe');
        $maximum = array_reduce($months, fn (string $a, string $b): string => $this->maximum($a, $b), '0.00');
        $period = $this->completedPeriod($months, $year, $openingDate, $today);
        $average = $period['month_count'] === 0
            ? null
            : $this->divideRounded($period['revenue'], (string) $period['month_count']);
        $monthly = [];
        foreach ($months as $month => $amount) {
            $monthly[] = [
                'month' => $month,
                'amount' => $amount,
                'height_percent' => bccomp($maximum, '0.00', 2) === 0 ? '0.00' : $this->divideRounded(bcmul($amount, '100', 2), $maximum),
            ];
        }

        return [
            'year' => $year,
            'as_of' => $today->format('Y-m-d'),
            'date_basis' => 'issue_date',
            'annual_revenue' => (string) $annual,
            'active_invoice_count' => $count,
            'months' => $monthly,
            'monthly_average' => [
                'amount' => $average,
                'height_percent' => $average === null ? null : (
                    bccomp($maximum, '0.00', 2) === 0
                        ? '0.00'
                        : $this->divideRounded(bcmul($average, '100', 2), $maximum)
                ),
                'month_count' => $period['month_count'],
            ],
            'ceiling' => [
                'amount' => (string) $limit,
                'used_percent' => (string) $percent,
                'status' => $state,
                'remaining_amount' => $this->maximum('0.00', bcsub($limit, $annual, 2)),
                'warning_percent' => $warningPercent,
                'danger_percent' => $dangerPercent,
            ],
            'projection' => $this->project($annual, $limit, $year, $today, $period),
        ];
    }

    /** @param array<int, string> $months
     * @return array{first_month: int, month_count: int, revenue: string, partial_revenue: string}
     */
    private function completedPeriod(array $months, int $year, ?string $openingDate, DateTimeImmutable $today): array
    {
        $empty = ['first_month' => 1, 'month_count' => 0, 'revenue' => '0.00', 'partial_revenue' => '0.00'];
        $currentYear = (int) $today->format('Y');
        if ($year > $currentYear || $openingDate === null || (int) substr($openingDate, 0, 4) > $year) {
            return $empty;
        }
        $firstMonth = 1;
        if ((int) substr($openingDate, 0, 4) === $year) {
            // Um mês de abertura parcial não compõe a média de meses completos.
            $firstMonth = (int) substr($openingDate, 5, 2) + (substr($openingDate, 8, 2) === '01' ? 0 : 1);
        }
        $lastCompleteMonth = $year < $currentYear ? self::MONTHS_PER_YEAR : (int) $today->format('n') - 1;
        $completedMonths = max(0, $lastCompleteMonth - $firstMonth + 1);
        if ($completedMonths === 0) {
            return $empty;
        }
        $completeRevenue = '0.00';
        $partialRevenue = '0.00';
        foreach ($months as $month => $amount) {
            if ($month < $firstMonth) {
                $partialRevenue = bcadd($partialRevenue, $amount, 2);
            } elseif ($month <= $lastCompleteMonth) {
                $completeRevenue = bcadd($completeRevenue, $amount, 2);
            }
        }

        return ['first_month' => $firstMonth, 'month_count' => $completedMonths,
            'revenue' => $completeRevenue, 'partial_revenue' => $partialRevenue];
    }

    /** @param array{first_month: int, month_count: int, revenue: string, partial_revenue: string} $period
     * @return array<string, mixed>
     */
    private function project(string $annual, string $limit, int $year, DateTimeImmutable $today, array $period): array
    {
        if ($year < (int) $today->format('Y')) {
            return ['amount' => $annual, 'status' => 'closed_year', 'completed_months' => null];
        }
        if ($period['month_count'] === 0) {
            return ['amount' => null, 'status' => 'insufficient_data', 'completed_months' => 0];
        }
        // A projeção usa a amostra exata, não a média já arredondada para exibição no gráfico.
        $projected = bcadd($this->divideRounded(
            bcmul($period['revenue'], (string) (self::MONTHS_PER_YEAR - $period['first_month'] + 1), 2),
            (string) $period['month_count'],
        ), $period['partial_revenue'], 2);
        $projected = $this->maximum($annual, $projected);

        return [
            'amount' => $projected,
            'status' => bccomp($projected, $limit, 2) > 0 ? 'over_limit' : 'within_limit',
            'completed_months' => $period['month_count'],
        ];
    }

    /** @param array<int, string> $amounts */
    private function sum(array $amounts): string
    {
        return array_reduce($amounts, fn (string $sum, string $amount): string => bcadd($sum, $amount, 2), '0.00');
    }

    private function maximum(string $left, string $right): string
    {
        return bccomp($left, $right, 2) >= 0 ? $left : $right;
    }

    /** Arredonda valores não negativos a centavos, sem conversão para float. */
    private function divideRounded(string $numerator, string $denominator): string
    {
        return bcadd(bcdiv($numerator, $denominator, 3), '0.005', 2);
    }
}
