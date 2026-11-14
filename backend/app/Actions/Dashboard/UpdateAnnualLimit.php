<?php

declare(strict_types=1);

namespace App\Actions\Dashboard;

use App\Models\Mei;
use App\Models\MeiAnnualLimit;

/** Grava uma configuração por MEI/ano sem persistir os agregados calculados. */
class UpdateAnnualLimit
{
    public function execute(Mei $mei, int $year, string $amount): void
    {
        MeiAnnualLimit::query()->upsert([
            ['mei_id' => $mei->id, 'year' => $year, 'amount' => $amount],
        ], ['mei_id', 'year'], ['amount', 'updated_at']);
    }
}
