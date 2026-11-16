<?php

declare(strict_types=1);

namespace App\Actions\Dashboard;

use App\Models\Mei;
use App\Models\MeiAnnualLimit;

/**
 * Grava somente a configuração de limite do exercício, nunca os totais calculados.
 *
 * O upsert torna a operação idempotente: a mesma chave MEI/ano cria ou substitui o valor.
 */
class UpdateAnnualLimit
{
    public function execute(Mei $mei, int $year, string $amount): void
    {
        MeiAnnualLimit::query()->upsert([
            ['mei_id' => $mei->id, 'year' => $year, 'amount' => $amount],
        ], ['mei_id', 'year'], ['amount', 'updated_at']);
    }
}
