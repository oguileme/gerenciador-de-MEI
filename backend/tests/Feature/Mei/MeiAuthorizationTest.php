<?php

declare(strict_types=1);

use App\Models\Mei;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function validMeiPayload(array $overrides = []): array
{
    return array_merge([
        'cnpj' => '12345678000199',
        'mei_name' => 'Empresa de Teste',
        'opening_date' => '2020-01-15',
        'ibge_municipality_code' => '3550308',
    ], $overrides);
}

it('exige autenticação em todas as rotas de MEI', function (): void {
    $mei = Mei::factory()->create();

    $this->getJson('/api/meis')->assertUnauthorized();
    $this->postJson('/api/meis', validMeiPayload())->assertUnauthorized();
    $this->getJson("/api/meis/{$mei->id}")->assertUnauthorized();
    $this->patchJson("/api/meis/{$mei->id}", ['mei_name' => 'Novo nome'])->assertUnauthorized();
    $this->deleteJson("/api/meis/{$mei->id}")->assertUnauthorized();
    $this->postJson("/api/meis/{$mei->id}/restore")->assertUnauthorized();
});

it('lista somente os MEIs ativos do usuário com paginação', function (): void {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $ownMeis = Mei::factory()->count(16)->for($user)->create();
    $otherMei = Mei::factory()->for($otherUser)->create([
        'cnpj' => $ownMeis->first()->cnpj,
    ]);

    $response = $this->actingAs($user)->getJson('/api/meis');

    $response
        ->assertOk()
        ->assertJsonCount(15, 'data')
        ->assertJsonPath('meta.total', 16)
        ->assertJsonMissing(['id' => $otherMei->id]);
});

it('associa o novo MEI ao usuário autenticado e não expõe user_id', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/api/meis', validMeiPayload());

    $response
        ->assertCreated()
        ->assertJsonPath('data.cnpj', '12345678000199')
        ->assertJsonMissingPath('data.user_id');
    $this->assertDatabaseHas('mei', [
        'cnpj' => '12345678000199',
        'user_id' => $user->id,
    ]);
});

it('rejeita tentativa de escolher o proprietário do MEI', function (): void {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $this->actingAs($user)
        ->postJson('/api/meis', validMeiPayload(['user_id' => $otherUser->id]))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('user_id');

    $this->assertDatabaseMissing('mei', ['cnpj' => '12345678000199']);
});

it('permite o mesmo CNPJ em contas diferentes mas não na mesma conta', function (): void {
    $firstUser = User::factory()->create();
    $secondUser = User::factory()->create();

    $this->actingAs($firstUser)
        ->postJson('/api/meis', validMeiPayload(['mei_name' => 'Primeira empresa']))
        ->assertCreated();

    $this->actingAs($secondUser)
        ->postJson('/api/meis', validMeiPayload(['mei_name' => 'Segunda empresa']))
        ->assertCreated();

    $this->actingAs($firstUser)
        ->postJson('/api/meis', validMeiPayload())
        ->assertUnprocessable()
        ->assertJsonValidationErrors('cnpj');

    $this->assertDatabaseCount('mei', 2);
});

it('permite ao proprietário visualizar atualizar excluir e restaurar seu MEI', function (): void {
    $user = User::factory()->create();
    $mei = Mei::factory()->for($user)->create();

    $this->actingAs($user)
        ->getJson("/api/meis/{$mei->id}")
        ->assertOk()
        ->assertJsonPath('data.id', $mei->id);

    $this->actingAs($user)
        ->patchJson("/api/meis/{$mei->id}", ['mei_name' => 'Nome atualizado'])
        ->assertOk()
        ->assertJsonPath('data.mei_name', 'Nome atualizado');

    $this->actingAs($user)
        ->deleteJson("/api/meis/{$mei->id}")
        ->assertNoContent();

    $this->assertSoftDeleted('mei', ['id' => $mei->id]);
    $this->actingAs($user)->getJson("/api/meis/{$mei->id}")->assertNotFound();

    $this->actingAs($user)
        ->postJson("/api/meis/{$mei->id}/restore")
        ->assertOk()
        ->assertJsonPath('data.id', $mei->id);

    $this->assertDatabaseHas('mei', [
        'id' => $mei->id,
        'deleted_at' => null,
    ]);
});

it('retorna conflito ao tentar restaurar um MEI ativo', function (): void {
    $user = User::factory()->create();
    $mei = Mei::factory()->for($user)->create();

    $this->actingAs($user)
        ->postJson("/api/meis/{$mei->id}/restore")
        ->assertConflict()
        ->assertJsonPath('message', 'O MEI já está ativo.');
});

it('oculta o MEI de outro usuário em todas as operações individuais', function (): void {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $mei = Mei::factory()->for($owner)->create(['mei_name' => 'Nome original']);
    $deletedMei = Mei::factory()->for($owner)->create();
    $deletedMei->delete();

    $this->actingAs($otherUser)->getJson("/api/meis/{$mei->id}")->assertNotFound();
    $this->actingAs($otherUser)
        ->patchJson("/api/meis/{$mei->id}", ['mei_name' => 'Tentativa indevida'])
        ->assertNotFound();
    $this->actingAs($otherUser)->deleteJson("/api/meis/{$mei->id}")->assertNotFound();
    $this->actingAs($otherUser)
        ->postJson("/api/meis/{$deletedMei->id}/restore")
        ->assertNotFound();

    expect($mei->fresh()->mei_name)->toBe('Nome original');
    $this->assertDatabaseHas('mei', [
        'id' => $deletedMei->id,
        'deleted_at' => $deletedMei->deleted_at,
    ]);
});

it('mantém o CNPJ reservado para restauração após exclusão', function (): void {
    $user = User::factory()->create();
    $mei = Mei::factory()->for($user)->create(['cnpj' => '12345678000199']);
    $mei->delete();

    $this->actingAs($user)
        ->postJson('/api/meis', validMeiPayload())
        ->assertUnprocessable()
        ->assertJsonValidationErrors('cnpj')
        ->assertJsonPath(
            'errors.cnpj.0',
            'Este CNPJ já está cadastrado para sua conta, inclusive entre os MEIs excluídos. Restaure o registro existente.'
        );
});
