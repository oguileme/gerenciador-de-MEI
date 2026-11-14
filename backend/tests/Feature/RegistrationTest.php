<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

it('permite cadastro em bancos antigos sem exigir data de nascimento', function (): void {
    Schema::table('users', function (Blueprint $table): void {
        $table->dropColumn('birth_date');
    });
    Schema::table('users', function (Blueprint $table): void {
        $table->date('birth_date');
    });

    $migration = require database_path('migrations/2026_11_07_000000_make_legacy_user_birth_date_nullable.php');
    $migration->up();
    config(['sanctum.stateful' => ['localhost:5173']]);

    $this->withHeader('Origin', 'http://localhost:5173')
        ->postJson('/api/register', [
            'name' => 'Pessoa de Teste',
            'email' => 'legacy@example.com',
            'password' => 'senha-ficticia-123',
            'password_confirmation' => 'senha-ficticia-123',
        ])->assertCreated();

    $this->assertDatabaseHas('users', ['email' => 'legacy@example.com', 'birth_date' => null]);
});

it('cadastra e autentica um usuário pela rota da API', function (): void {
    config(['sanctum.stateful' => ['localhost:5173']]);

    $response = $this->withHeader('Origin', 'http://localhost:5173')
        ->postJson('/api/register', [
            'name' => 'Pessoa de Teste',
            'email' => 'pessoa@example.com',
            'password' => 'senha-ficticia-123',
            'password_confirmation' => 'senha-ficticia-123',
        ]);

    $response->assertCreated()
        ->assertJsonPath('data.email', 'pessoa@example.com')
        ->assertJsonMissingPath('data.password')
        ->assertJsonMissingPath('data.remember_token');

    $user = User::query()->sole();
    expect(Hash::check('senha-ficticia-123', $user->password))->toBeTrue();
    $this->assertAuthenticatedAs($user, 'web');
    $this->getJson('/api/user')->assertOk()->assertJsonPath('data.id', $user->id);
});

it('retorna erros de validação ao cadastrar sem os campos obrigatórios', function (): void {
    config(['sanctum.stateful' => ['localhost:5173']]);

    $this->withHeader('Origin', 'http://localhost:5173')
        ->postJson('/api/register', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['name', 'email', 'password']);

    $this->assertDatabaseCount('users', 0);
});
