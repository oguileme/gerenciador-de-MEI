<?php

declare(strict_types=1);

use App\Models\Mei;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

function importInvoiceXml(string $version = '1.01'): string
{
    $contents = file_get_contents(__DIR__."/../../Fixtures/Nfse/national-v{$version}.xml");

    if (! is_string($contents)) {
        throw new RuntimeException('Fixture de NFS-e não encontrada.');
    }

    return $contents;
}

function invoiceUpload(string $xml): UploadedFile
{
    return UploadedFile::fake()->createWithContent('nfse.xml', $xml);
}

beforeEach(function (): void {
    Storage::fake('invoice_xml');
    Storage::fake('public');
    config(['invoices.xml_disk' => 'invoice_xml']);
});

it('exige autenticação para importar uma NFS-e', function (): void {
    $mei = Mei::factory()->create(['cnpj' => '12345678000199']);

    $this->withHeader('Accept', 'application/json')
        ->post("/api/meis/{$mei->id}/invoices/import", [
            'xml' => invoiceUpload(importInvoiceXml()),
        ])
        ->assertUnauthorized();
});

it('oculta de outro usuário o MEI usado na importação', function (): void {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $mei = Mei::factory()->for($owner)->create(['cnpj' => '12345678000199']);

    $this->actingAs($otherUser)
        ->post("/api/meis/{$mei->id}/invoices/import", [
            'xml' => invoiceUpload(importInvoiceXml()),
        ])
        ->assertNotFound();

    $this->assertDatabaseEmpty('invoices');
});

it('importa a NFS-e e preserva o XML somente no disco privado', function (): void {
    $user = User::factory()->create();
    $mei = Mei::factory()->for($user)->create(['cnpj' => '12345678000199']);
    $xml = importInvoiceXml();
    $hash = hash('sha256', $xml);
    $path = "xml/{$mei->id}/{$hash}.xml";

    $response = $this->actingAs($user)
        ->post("/api/meis/{$mei->id}/invoices/import", [
            'xml' => invoiceUpload($xml),
        ]);

    $response
        ->assertCreated()
        ->assertJsonPath('data.mei_id', $mei->id)
        ->assertJsonPath('data.type', 'nfse')
        ->assertJsonPath('data.status', 'active')
        ->assertJsonPath('data.service_amount', '1234.56')
        ->assertJsonPath('data.net_amount', '1234.55')
        ->assertJsonPath('data.issue_date', '2025-12-31')
        ->assertJsonPath('data.competence_date', '2025-12-15')
        ->assertJsonMissingPath('data.xml_path')
        ->assertJsonMissingPath('data.xml_sha256');

    $this->assertDatabaseHas('invoices', [
        'mei_id' => $mei->id,
        'access_key' => '12345678901234567890123456789012345678901234567890',
        'service_amount' => '1234.56',
        'net_amount' => '1234.55',
        'xml_path' => $path,
        'xml_sha256' => $hash,
    ]);
    Storage::disk('invoice_xml')->assertExists($path);
    expect(Storage::disk('invoice_xml')->get($path))->toBe($xml);
    Storage::disk('public')->assertMissing($path);
});

it('rejeita NFS-e cujo CNPJ não pertence ao MEI selecionado', function (): void {
    $user = User::factory()->create();
    $mei = Mei::factory()->for($user)->create(['cnpj' => '11111111000191']);

    $this->actingAs($user)
        ->post("/api/meis/{$mei->id}/invoices/import", [
            'xml' => invoiceUpload(importInvoiceXml()),
        ])
        ->assertUnprocessable()
        ->assertJsonPath('errors.xml.0', 'O CNPJ da NFS-e não corresponde ao MEI selecionado.');

    $this->assertDatabaseEmpty('invoices');
    expect(Storage::disk('invoice_xml')->allFiles())->toBeEmpty();
});

it('rejeita conteúdo que não seja um XML válido sem criar artefatos', function (): void {
    $user = User::factory()->create();
    $mei = Mei::factory()->for($user)->create(['cnpj' => '12345678000199']);

    $this->actingAs($user)
        ->post("/api/meis/{$mei->id}/invoices/import", [
            'xml' => invoiceUpload('<NFSe>'),
        ])
        ->assertUnprocessable()
        ->assertJsonPath('errors.xml.0', 'O arquivo não é um XML válido.');

    $this->assertDatabaseEmpty('invoices');
    expect(Storage::disk('invoice_xml')->allFiles())->toBeEmpty();
});

it('não aceita campos fiscais enviados pelo cliente', function (): void {
    $user = User::factory()->create();
    $mei = Mei::factory()->for($user)->create(['cnpj' => '12345678000199']);

    $this->actingAs($user)
        ->post("/api/meis/{$mei->id}/invoices/import", [
            'xml' => invoiceUpload(importInvoiceXml()),
            'service_amount' => '0.01',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('service_amount');

    $this->assertDatabaseEmpty('invoices');
});

it('rejeita o mesmo arquivo e a mesma chave sem duplicar a nota', function (): void {
    $user = User::factory()->create();
    $mei = Mei::factory()->for($user)->create(['cnpj' => '12345678000199']);
    $xml = importInvoiceXml();

    $this->actingAs($user)
        ->post("/api/meis/{$mei->id}/invoices/import", ['xml' => invoiceUpload($xml)])
        ->assertCreated();

    $this->actingAs($user)
        ->post("/api/meis/{$mei->id}/invoices/import", ['xml' => invoiceUpload($xml)])
        ->assertConflict();

    $sameKeyDifferentFile = str_replace(
        '<xLocEmi>São Paulo</xLocEmi>',
        "<xLocEmi>São Paulo</xLocEmi>\n    ",
        $xml
    );

    $this->actingAs($user)
        ->post("/api/meis/{$mei->id}/invoices/import", [
            'xml' => invoiceUpload($sameKeyDifferentFile),
        ])
        ->assertConflict();

    $this->assertDatabaseCount('invoices', 1);
    expect(Storage::disk('invoice_xml')->allFiles())->toHaveCount(1);
});

it('aplica o limite configurado ao upload', function (): void {
    config(['invoices.xml_max_kilobytes' => 1]);
    $user = User::factory()->create();
    $mei = Mei::factory()->for($user)->create(['cnpj' => '12345678000199']);

    $this->actingAs($user)
        ->post("/api/meis/{$mei->id}/invoices/import", [
            'xml' => invoiceUpload(str_repeat('x', 2048)),
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('xml');
});

it('responde de forma genérica quando o storage falha', function (): void {
    config(['invoices.xml_disk' => 'disco_inexistente']);
    $user = User::factory()->create();
    $mei = Mei::factory()->for($user)->create(['cnpj' => '12345678000199']);

    $this->actingAs($user)
        ->post("/api/meis/{$mei->id}/invoices/import", [
            'xml' => invoiceUpload(importInvoiceXml()),
        ])
        ->assertInternalServerError()
        ->assertJsonPath('message', 'Não foi possível concluir a importação da NFS-e.');

    $this->assertDatabaseEmpty('invoices');
});
