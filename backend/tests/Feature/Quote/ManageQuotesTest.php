<?php

declare(strict_types=1);

use App\Models\Mei;
use App\Models\Quote;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

function validQuotePayload(array $overrides = []): array
{
    return array_replace([
        'recipient_name' => 'Cliente Fictício',
        'items' => [
            ['description' => 'Consultoria', 'unit_price' => '100.00', 'quantity' => '1.5'],
            ['description' => 'Ajuste', 'unit_price' => '0.01', 'quantity' => '0.5'],
        ],
    ], $overrides);
}

function createQuoteThroughApi(User $user, Mei $mei, array $payload = []): Quote
{
    test()->actingAs($user)
        ->postJson("/api/meis/{$mei->id}/quotes", $payload ?: validQuotePayload())
        ->assertCreated();

    return Quote::query()->latest('id')->firstOrFail();
}

it('cria orçamento com data brasileira, emitente histórico e totais calculados', function (): void {
    Carbon::setTestNow(Carbon::parse('2026-11-15 01:30:00', 'UTC'));
    try {
        $user = User::factory()->create();
        $mei = Mei::factory()->for($user)->create(['mei_name' => 'Prestador Original']);

        $response = $this->actingAs($user)->postJson(
            "/api/meis/{$mei->id}/quotes",
            validQuotePayload(),
        );

        $response->assertCreated()
            ->assertJsonPath('data.issuer_name', 'Prestador Original')
            ->assertJsonPath('data.issue_date', '2026-11-14')
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.total_amount', '150.01')
            ->assertJsonPath('data.items.0.quantity', '1.500')
            ->assertJsonPath('data.items.1.subtotal', '0.01');
        $quoteId = $response->json('data.id');

        $mei->update(['mei_name' => 'Prestador Renomeado']);
        $this->getJson("/api/meis/{$mei->id}/quotes/{$quoteId}")
            ->assertOk()
            ->assertJsonPath('data.issuer_name', 'Prestador Original');
        $this->assertDatabaseCount('quotes', 1);
        $this->assertDatabaseCount('quote_items', 2);
    } finally {
        Carbon::setTestNow();
    }
});

it('rejeita campos controlados pelo servidor e coleções inválidas sem persistir parcialmente', function (): void {
    $user = User::factory()->create();
    $mei = Mei::factory()->for($user)->create();
    $payload = validQuotePayload([
        'issuer_name' => 'Forjado',
        'issue_date' => '2020-01-01',
        'status' => 'accepted',
        'total_amount' => '0.01',
    ]);
    $payload['items'][0]['subtotal'] = '0.01';

    $this->actingAs($user)->postJson("/api/meis/{$mei->id}/quotes", $payload)
        ->assertUnprocessable()
        ->assertJsonValidationErrors([
            'issuer_name', 'issue_date', 'status', 'total_amount', 'items.0.subtotal',
        ]);
    $this->postJson("/api/meis/{$mei->id}/quotes", ['recipient_name' => 'Cliente', 'items' => []])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('items');
    $this->postJson("/api/meis/{$mei->id}/quotes", [
        'recipient_name' => 'Cliente',
        'items' => array_fill(0, 101, [
            'description' => 'Serviço', 'unit_price' => '1.00', 'quantity' => '1',
        ]),
    ])->assertUnprocessable()->assertJsonValidationErrors('items');
    $this->postJson("/api/meis/{$mei->id}/quotes", validQuotePayload([
        'items' => [['description' => 'Inválido', 'unit_price' => '0.00', 'quantity' => '1']],
    ]))->assertUnprocessable()->assertJsonValidationErrors('items.0.unit_price');

    $this->assertDatabaseCount('quotes', 0);
    $this->assertDatabaseCount('quote_items', 0);
});

it('lista com paginação e filtro sem expor orçamentos de outro MEI', function (): void {
    $user = User::factory()->create();
    $mei = Mei::factory()->for($user)->create();
    $otherMei = Mei::factory()->for($user)->create();
    $pending = createQuoteThroughApi($user, $mei);
    $accepted = createQuoteThroughApi($user, $mei);
    createQuoteThroughApi($user, $otherMei);
    $this->patchJson("/api/meis/{$mei->id}/quotes/{$accepted->id}/status", ['status' => 'accepted'])
        ->assertOk();

    $this->getJson("/api/meis/{$mei->id}/quotes?status=pending")
        ->assertOk()
        ->assertJsonPath('meta.total', 1)
        ->assertJsonPath('data.0.id', $pending->id)
        ->assertJsonMissingPath('data.0.items');
    $this->getJson("/api/meis/{$mei->id}/quotes?status=unknown")
        ->assertUnprocessable();
});

it('substitui conteúdo apenas enquanto pendente e aplica o ciclo de status', function (): void {
    $user = User::factory()->create();
    $mei = Mei::factory()->for($user)->create();
    $quote = createQuoteThroughApi($user, $mei);
    $url = "/api/meis/{$mei->id}/quotes/{$quote->id}";

    $this->putJson($url, validQuotePayload([
        'recipient_name' => 'Cliente Atualizado',
        'items' => [['description' => 'Novo serviço', 'unit_price' => '12.50', 'quantity' => '2']],
    ]))->assertOk()
        ->assertJsonPath('data.recipient_name', 'Cliente Atualizado')
        ->assertJsonPath('data.total_amount', '25.00')
        ->assertJsonCount(1, 'data.items');
    $this->assertDatabaseCount('quote_items', 1);

    $this->patchJson("{$url}/status", ['status' => 'accepted'])
        ->assertOk()->assertJsonPath('data.status', 'accepted');
    $this->putJson($url, validQuotePayload())->assertConflict();
    $this->deleteJson($url)->assertConflict();
    $this->patchJson("{$url}/status", ['status' => 'rejected'])->assertConflict();
    $this->patchJson("{$url}/status", ['status' => 'pending'])->assertOk();
    $this->patchJson("{$url}/status", ['status' => 'rejected'])->assertOk();
    $this->patchJson("{$url}/status", ['status' => 'pending'])->assertOk();
    $this->deleteJson($url)->assertNoContent();
    $this->assertDatabaseMissing('quotes', ['id' => $quote->id]);
    $this->assertDatabaseCount('quote_items', 0);
});

it('exige autenticação e oculta recursos de outros usuários ou MEIs', function (): void {
    $owner = User::factory()->create();
    $mei = Mei::factory()->for($owner)->create();
    $quote = createQuoteThroughApi($owner, $mei);
    $otherMei = Mei::factory()->for($owner)->create();
    $url = "/api/meis/{$mei->id}/quotes/{$quote->id}";

    $this->app['auth']->forgetGuards();
    $this->getJson($url)->assertUnauthorized();
    $this->actingAs(User::factory()->create())->getJson($url)->assertNotFound();
    $this->actingAs($owner)->getJson("/api/meis/{$otherMei->id}/quotes/{$quote->id}")
        ->assertNotFound();
    $this->putJson("/api/meis/{$otherMei->id}/quotes/{$quote->id}", validQuotePayload())
        ->assertNotFound();
});

it('baixa PDF autorizado com nome estável e assinatura válida', function (): void {
    $user = User::factory()->create();
    $mei = Mei::factory()->for($user)->create();
    $quote = createQuoteThroughApi($user, $mei);

    $response = $this->get("/api/meis/{$mei->id}/quotes/{$quote->id}/pdf");

    $response->assertOk()
        ->assertHeader('Content-Type', 'application/pdf')
        ->assertHeader('Content-Disposition', "attachment; filename=\"orcamento-{$quote->id}.pdf\"");
    expect($response->getContent())->toStartWith('%PDF-');
});
