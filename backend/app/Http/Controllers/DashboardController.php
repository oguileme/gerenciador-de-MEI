<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Dashboard\GetDashboard;
use App\Actions\Dashboard\UpdateAnnualLimit;
use App\Http\Requests\Dashboard\DashboardRequest;
use App\Http\Requests\Dashboard\UpdateAnnualLimitRequest;
use App\Http\Resources\DashboardResource;
use App\Models\Mei;

/** Traduz requisições do painel em ações de domínio, sem calcular valores no controller. */
class DashboardController extends Controller
{
    public function show(DashboardRequest $request, Mei $mei, GetDashboard $action): DashboardResource
    {
        // O dia fiscal segue São Paulo; depender do fuso do servidor deslocaria
        // viradas de mês e ano.
        $today = now('America/Sao_Paulo')->toDateTimeImmutable();
        $year = (int) $request->validated('year', $today->format('Y'));

        return new DashboardResource($action->execute($mei, $year, $today));
    }

    public function updateLimit(UpdateAnnualLimitRequest $request, Mei $mei, GetDashboard $action, UpdateAnnualLimit $update): DashboardResource
    {
        $year = (int) $request->validated('year');
        $update->execute($mei, $year, $request->validated('amount'));

        return new DashboardResource($action->execute($mei, $year, now('America/Sao_Paulo')->toDateTimeImmutable()));
    }
}
