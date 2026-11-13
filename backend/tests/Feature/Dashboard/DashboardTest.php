<?php

declare(strict_types=1);

use App\Models\Mei;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('calcula métricas por emissão com isolamento entre MEIs e strings decimais', function (): void {
    $this->travelTo(now()->setDate(2026, 7, 12));
    $user = User::factory()->create();
    $mei = Mei::factory()->for($user)->create(['opening_date' => '2020-01-01']);
    $other = Mei::factory()->for($user)->create();
    dashboardInvoice($mei, ['service_amount' => '12.34']);
    dashboardInvoice($mei, ['service_amount' => '0.01']);
    dashboardInvoice($mei, ['status' => 'cancelled']);
    dashboardInvoice($mei, ['status' => 'substituted']);
    dashboardInvoice($other);
    $this->actingAs($user)->getJson("/api/meis/{$mei->id}/dashboard?year=2026")
        ->assertOk()->assertJsonPath('data.annual_revenue', '12.35')
        ->assertJsonPath('data.months.0.amount', '12.35')
        ->assertJsonPath('data.monthly_average.amount', '2.06')
        ->assertJsonPath('data.monthly_average.month_count', 6)
        ->assertJsonPath('data.active_invoice_count', 2)
        ->assertJsonPath('data.ceiling.amount', '81000.00')
        ->assertJsonPath('data.date_basis', 'issue_date');
});

it('salva limite por ano sem modificar outros anos ou contas', function (): void {
    $user = User::factory()->create();
    $mei = Mei::factory()->for($user)->create();
    $this->actingAs($user)->putJson("/api/meis/{$mei->id}/annual-limit", ['year' => 2026, 'amount' => '95000.01'])
        ->assertOk()->assertJsonPath('data.ceiling.amount', '95000.01');
    $this->getJson("/api/meis/{$mei->id}/dashboard?year=2025")
        ->assertOk()->assertJsonPath('data.ceiling.amount', '81000.00');
    $this->putJson("/api/meis/{$mei->id}/annual-limit", ['year' => 2026, 'amount' => '100.01'])->assertOk();
    $this->assertDatabaseCount('mei_annual_limits', 1);
    foreach (['0.00', '-1.00', '1.999', '1e3', '10000000000000.00', 100] as $invalid) {
        $this->putJson("/api/meis/{$mei->id}/annual-limit", ['year' => 2026, 'amount' => $invalid])->assertUnprocessable();
    }
});

it('exige autenticação e autorização para métricas e limite', function (): void {
    $mei = Mei::factory()->create();
    $this->getJson("/api/meis/{$mei->id}/dashboard")->assertUnauthorized();
    $this->putJson("/api/meis/{$mei->id}/annual-limit", ['year' => 2026, 'amount' => '1.00'])->assertUnauthorized();
    $this->actingAs(User::factory()->create())->getJson("/api/meis/{$mei->id}/dashboard")->assertNotFound();
    $this->putJson("/api/meis/{$mei->id}/annual-limit", ['year' => 2026, 'amount' => '1.00'])->assertNotFound();
});
