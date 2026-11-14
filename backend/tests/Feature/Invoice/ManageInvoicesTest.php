<?php

declare(strict_types=1);

use App\Models\Mei;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('lista apenas notas do MEI solicitado com paginação e filtro de status', function (): void {
    $user = User::factory()->create();
    $mei = Mei::factory()->for($user)->create();
    $other = Mei::factory()->for($user)->create();
    dashboardInvoice($other);
    dashboardInvoice($mei, ['status' => 'cancelled']);
    for ($i = 0; $i < 11; $i++) {
        dashboardInvoice($mei);
    }
    $this->actingAs($user)->getJson("/api/meis/{$mei->id}/invoices?status=active")
        ->assertOk()->assertJsonCount(10, 'data')->assertJsonPath('meta.total', 11)
        ->assertJsonPath('data.0.service_amount', '1234.56')
        ->assertJsonMissingPath('data.0.xml_path');
    $this->getJson("/api/meis/{$mei->id}/invoices?status=active&page=2")
        ->assertOk()->assertJsonCount(1, 'data');
});

it('altera somente status sem remover nota nem modificar dados fiscais', function (): void {
    $user = User::factory()->create();
    $mei = Mei::factory()->for($user)->create();
    $invoice = dashboardInvoice($mei);
    foreach (['cancelled', 'substituted', 'active'] as $status) {
        $this->actingAs($user)->patchJson("/api/meis/{$mei->id}/invoices/{$invoice->id}/status", [
            'status' => $status,
        ])->assertOk()->assertJsonPath('data.status', $status);
    }
    expect($invoice->fresh()->service_amount)->toBe('1234.56');
    expect($invoice->fresh()->xml_path)->toBe('xml/ficticio.xml');
    $this->assertDatabaseCount('invoices', 1);
    $this->patchJson("/api/meis/{$mei->id}/invoices/{$invoice->id}/status", [
        'status' => 'active', 'service_amount' => '0.01',
    ])->assertUnprocessable();
    $this->patchJson("/api/meis/{$mei->id}/invoices/{$invoice->id}/status", [
        'status' => 'deleted',
    ])->assertUnprocessable();
});

it('exige sessão e oculta notas sem vínculo ou de outro MEI da mesma conta', function (): void {
    $owner = User::factory()->create();
    $mei = Mei::factory()->for($owner)->create();
    $invoice = dashboardInvoice($mei);
    $url = "/api/meis/{$mei->id}/invoices";
    $this->getJson($url)->assertUnauthorized();
    $this->patchJson("{$url}/{$invoice->id}/status", ['status' => 'cancelled'])->assertUnauthorized();
    $this->actingAs(User::factory()->create())->getJson($url)->assertNotFound();
    $this->patchJson("{$url}/{$invoice->id}/status", ['status' => 'cancelled'])->assertNotFound();
    $other = Mei::factory()->for($owner)->create();
    $this->actingAs($owner)->patchJson("/api/meis/{$other->id}/invoices/{$invoice->id}/status", [
        'status' => 'cancelled',
    ])->assertNotFound();
    expect($invoice->fresh()->status->value)->toBe('active');
});
