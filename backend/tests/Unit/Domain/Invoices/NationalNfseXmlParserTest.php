<?php

declare(strict_types=1);

use App\Domain\Invoices\Exceptions\InvalidInvoiceXml;
use App\Domain\Invoices\NationalNfseXmlParser;

function nfseFixture(string $version = '1.01'): string
{
    $contents = file_get_contents(__DIR__."/../../../Fixtures/Nfse/national-v{$version}.xml");

    if (! is_string($contents)) {
        throw new RuntimeException('Fixture de NFS-e não encontrada.');
    }

    return $contents;
}

function nfseParser(): NationalNfseXmlParser
{
    return new NationalNfseXmlParser(__DIR__.'/../../../../resources/schemas/nfse');
}

it('extrai os dados fiscais exatos dos layouts nacionais suportados', function (string $version): void {
    $data = nfseParser()->parse(nfseFixture($version));

    expect($data->accessKey)->toBe('12345678901234567890123456789012345678901234567890')
        ->and($data->invoiceNumber)->toBe('123')
        ->and($data->issuerCnpj)->toBe('12345678000199')
        ->and($data->providerCnpj)->toBe('12345678000199')
        ->and($data->customerName)->toBe('Cliente Fictício Ltda.')
        ->and($data->serviceAmount)->toBe('1234.56')
        ->and($data->netAmount)->toBe('1234.55')
        ->and($data->issueDate)->toBe('2025-12-31')
        ->and($data->competenceDate)->toBe('2025-12-15')
        ->and($data->description)->toBe('Desenvolvimento de sistema de teste');
})->with(['1.00', '1.01']);

it('aceita NFS-e sem tomador', function (): void {
    $xml = preg_replace('/\s*<toma>.*?<\/toma>/s', '', nfseFixture());

    expect($xml)->toBeString();

    $data = nfseParser()->parse($xml);

    expect($data->customerName)->toBeNull();
});

it('rejeita doctype antes de carregar o documento', function (): void {
    $xml = str_replace(
        '<NFSe ',
        '<!DOCTYPE NFSe [<!ENTITY secret SYSTEM "file:///etc/passwd">]>'."\n".'<NFSe ',
        nfseFixture()
    );

    expect(fn () => nfseParser()->parse($xml))
        ->toThrow(InvalidInvoiceXml::class, 'XML com DOCTYPE não é permitido.');
});

it('rejeita XML malformado', function (): void {
    expect(fn () => nfseParser()->parse('<NFSe>'))
        ->toThrow(InvalidInvoiceXml::class, 'O arquivo não é um XML válido.');
});

it('rejeita namespace e versão não suportados', function (string $xml): void {
    expect(fn () => nfseParser()->parse($xml))->toThrow(InvalidInvoiceXml::class);
})->with([
    'namespace municipal' => fn (): string => str_replace(
        'http://www.sped.fazenda.gov.br/nfse',
        'https://prefeitura.example/nfse',
        nfseFixture()
    ),
    'versão futura' => fn (): string => str_replace('versao="1.01"', 'versao="2.00"', nfseFixture()),
]);

it('rejeita XML de homologação', function (): void {
    $xml = preg_replace('/<tpAmb>1<\/tpAmb>/', '<tpAmb>2</tpAmb>', nfseFixture(), 1);

    expect($xml)->toBeString();
    expect(fn () => nfseParser()->parse($xml))
        ->toThrow(InvalidInvoiceXml::class, 'Somente NFS-e do ambiente de produção pode ser importada.');
});

it('rejeita documento sem campo obrigatório ou assinatura estrutural', function (string $xml): void {
    expect(fn () => nfseParser()->parse($xml))
        ->toThrow(InvalidInvoiceXml::class, 'O XML não segue o esquema oficial da NFS-e nacional.');
})->with([
    'descrição ausente' => fn (): string => str_replace(
        '<xDescServ>Desenvolvimento de sistema de teste</xDescServ>',
        '',
        nfseFixture()
    ),
    'assinatura ausente' => fn (): string => preg_replace(
        '/\s*<Signature xmlns="http:\/\/www\.w3\.org\/2000\/09\/xmldsig#">.*?<\/Signature>/s',
        '',
        nfseFixture()
    ) ?? '',
]);
