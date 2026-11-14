<?php

declare(strict_types=1);

use App\Domain\Revenue\CalculateRevenue;
use App\Domain\Revenue\RevenueEntry;

function calculateRevenue(array $entries = [], string $today = '2026-07-12', ?string $opening = '2020-01-01', string $limit = '81000.00', int $year = 2026): array
{
    return (new CalculateRevenue)->calculate($entries, $year, $limit, $opening, new DateTimeImmutable($today), 80, 95);
}

it('soma centavos por emissão e exclui notas canceladas substituídas e de outro ano', function (): void {
    $result = calculateRevenue([
        new RevenueEntry('0.10', '2026-01-31', 'active'),
        new RevenueEntry('0.20', '2026-02-01', 'active'),
        new RevenueEntry('9000.00', '2026-01-01', 'cancelled'),
        new RevenueEntry('9000.00', '2026-01-01', 'substituted'),
        new RevenueEntry('9000.00', '2025-12-31', 'active'),
    ]);
    expect($result['annual_revenue'])->toBe('0.30')
        ->and($result['active_invoice_count'])->toBe(2)
        ->and($result['months'])->toHaveCount(12)
        ->and($result['months'][0]['amount'])->toBe('0.10')
        ->and($result['months'][1]['amount'])->toBe('0.20')
        ->and($result['months'][2]['amount'])->toBe('0.00');
});

it('classifica as faixas com precisão antes de arredondar o percentual', function (string $amount, string $status): void {
    $result = calculateRevenue([new RevenueEntry($amount, '2026-01-01', 'active')], limit: '100.00');
    expect($result['ceiling']['status'])->toBe($status);
})->with([
    ['79.99', 'safe'], ['80.00', 'warning'], ['94.99', 'warning'],
    ['95.00', 'danger'], ['100.00', 'danger'], ['125.00', 'danger'],
]);

it('não reduz o limite anual no ano de abertura e nunca retorna saldo negativo', function (): void {
    $result = calculateRevenue([new RevenueEntry('90000.01', '2026-06-01', 'active')], opening: '2026-06-01');
    expect($result['ceiling']['amount'])->toBe('81000.00')
        ->and($result['ceiling']['remaining_amount'])->toBe('0.00');
});

it('inclui meses completos sem receita na média e exclui o mês corrente da amostra', function (): void {
    $result = calculateRevenue([
        new RevenueEntry('12000.00', '2026-01-01', 'active'),
        new RevenueEntry('1000.00', '2026-07-01', 'active'),
    ]);
    expect($result['projection']['amount'])->toBe('24000.00')
        ->and($result['projection']['completed_months'])->toBe(6)
        ->and($result['projection']['status'])->toBe('within_limit');
});

it('exclui o mês parcial de abertura da média mas preserva sua receita na projeção', function (): void {
    $result = calculateRevenue([
        new RevenueEntry('300.00', '2026-03-15', 'active'),
        new RevenueEntry('1000.00', '2026-04-01', 'active'),
    ], today: '2026-05-01', opening: '2026-03-15');
    expect($result['projection']['amount'])->toBe('9300.00')
        ->and($result['projection']['completed_months'])->toBe(1);
});

it('não projeta abaixo da receita já registrada', function (): void {
    $result = calculateRevenue([
        new RevenueEntry('100.00', '2026-01-01', 'active'),
        new RevenueEntry('85000.00', '2026-07-01', 'active'),
    ]);
    expect($result['projection']['amount'])->toBe('85100.00')
        ->and($result['projection']['status'])->toBe('over_limit');
});

it('não inventa projeção sem meses completos ou sem data de abertura', function (string $today, ?string $opening): void {
    expect(calculateRevenue(today: $today, opening: $opening)['projection']['status'])->toBe('insufficient_data');
})->with([
    ['2026-01-31', '2020-01-01'], ['2026-06-30', '2026-06-01'],
    ['2026-07-15', '2026-06-15'], ['2026-07-15', null],
]);

it('mostra valor realizado para ano encerrado e aceita zero sem dividir por zero', function (): void {
    $closed = calculateRevenue([new RevenueEntry('2.01', '2025-12-31', 'active')], year: 2025);
    expect($closed['projection']['status'])->toBe('closed_year')
        ->and($closed['projection']['amount'])->toBe('2.01');
    $empty = calculateRevenue();
    expect($empty['annual_revenue'])->toBe('0.00')
        ->and($empty['months'][0]['height_percent'])->toBe('0.00')
        ->and($empty['projection']['amount'])->toBe('0.00');
});

it('calcula a média da linha pelos mesmos meses completos da projeção', function (): void {
    $result = calculateRevenue([
        new RevenueEntry('12000.00', '2026-01-01', 'active'),
        new RevenueEntry('60000.00', '2026-07-01', 'active'),
        new RevenueEntry('9000.00', '2026-02-01', 'cancelled'),
    ]);
    expect($result['monthly_average'])->toBe([
        'amount' => '2000.00', 'height_percent' => '3.33', 'month_count' => 6,
    ]);
});

it('exclui mês parcial de abertura e preserva precisão da projeção ao arredondar a média', function (): void {
    $result = calculateRevenue([
        new RevenueEntry('500.00', '2026-01-15', 'active'),
        new RevenueEntry('1.00', '2026-02-01', 'active'),
    ], today: '2026-05-01', opening: '2026-01-15');
    expect($result['monthly_average']['amount'])->toBe('0.33')
        ->and($result['monthly_average']['month_count'])->toBe(3)
        ->and($result['projection']['amount'])->toBe('503.67');
});

it('distingue média zero de período sem meses completos', function (): void {
    expect(calculateRevenue()['monthly_average'])->toBe([
        'amount' => '0.00', 'height_percent' => '0.00', 'month_count' => 6,
    ]);
    expect(calculateRevenue(today: '2026-01-31')['monthly_average'])->toBe([
        'amount' => null, 'height_percent' => null, 'month_count' => 0,
    ]);
});

it('considera todos os meses completos de atividade de um ano encerrado', function (): void {
    $result = calculateRevenue([
        new RevenueEntry('100.00', '2025-12-01', 'active'),
    ], year: 2025, opening: '2025-10-15');
    expect($result['monthly_average'])->toBe([
        'amount' => '50.00', 'height_percent' => '50.00', 'month_count' => 2,
    ]);
});
