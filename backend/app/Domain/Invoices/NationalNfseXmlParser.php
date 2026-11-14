<?php

declare(strict_types=1);

namespace App\Domain\Invoices;

use App\Domain\Invoices\Exceptions\InvalidInvoiceXml;
use DateTimeImmutable;
use DateTimeZone;
use DOMDocument;
use DOMElement;
use DOMXPath;
use Throwable;

final class NationalNfseXmlParser implements InvoiceXmlParser
{
    private const NAMESPACE = 'http://www.sped.fazenda.gov.br/nfse';

    private const SUPPORTED_VERSIONS = ['1.00', '1.01'];

    public function __construct(private readonly string $schemaDirectory) {}

    public function parse(string $xml): ParsedInvoiceData
    {
        if (preg_match('/<!DOCTYPE/i', $xml) === 1) {
            throw new InvalidInvoiceXml('XML com DOCTYPE não é permitido.');
        }

        $document = $this->loadDocument($xml);
        $root = $document->documentElement;

        if (! $root instanceof DOMElement || $root->localName !== 'NFSe' || $root->namespaceURI !== self::NAMESPACE) {
            throw new InvalidInvoiceXml('O arquivo não é uma NFS-e do padrão nacional.');
        }

        $version = $root->getAttribute('versao');

        if (! in_array($version, self::SUPPORTED_VERSIONS, true)) {
            throw new InvalidInvoiceXml('A versão do XML da NFS-e não é suportada.');
        }

        $this->validateSchema($document, $version);

        $xpath = new DOMXPath($document);
        $xpath->registerNamespace('n', self::NAMESPACE);

        $environment = $this->requiredValue($xpath, '/n:NFSe/n:infNFSe/n:DPS/n:infDPS/n:tpAmb');

        if ($environment !== '1') {
            throw new InvalidInvoiceXml('Somente NFS-e do ambiente de produção pode ser importada.');
        }

        $identifier = $this->requiredValue($xpath, '/n:NFSe/n:infNFSe/@Id');

        if (preg_match('/^NFS([0-9]{50})$/', $identifier, $matches) !== 1) {
            throw new InvalidInvoiceXml('A chave de acesso da NFS-e é inválida.');
        }

        return new ParsedInvoiceData(
            accessKey: $matches[1],
            invoiceNumber: $this->requiredValue($xpath, '/n:NFSe/n:infNFSe/n:nNFSe'),
            issuerCnpj: $this->requiredValue($xpath, '/n:NFSe/n:infNFSe/n:emit/n:CNPJ'),
            providerCnpj: $this->requiredValue($xpath, '/n:NFSe/n:infNFSe/n:DPS/n:infDPS/n:prest/n:CNPJ'),
            customerName: $this->optionalValue($xpath, '/n:NFSe/n:infNFSe/n:DPS/n:infDPS/n:toma/n:xNome'),
            serviceAmount: $this->normalizeAmount(
                $this->requiredValue($xpath, '/n:NFSe/n:infNFSe/n:DPS/n:infDPS/n:valores/n:vServPrest/n:vServ')
            ),
            netAmount: $this->normalizeAmount(
                $this->requiredValue($xpath, '/n:NFSe/n:infNFSe/n:valores/n:vLiq')
            ),
            issueDate: $this->issueDate(
                $this->requiredValue($xpath, '/n:NFSe/n:infNFSe/n:dhProc')
            ),
            competenceDate: $this->requiredValue($xpath, '/n:NFSe/n:infNFSe/n:DPS/n:infDPS/n:dCompet'),
            description: $this->requiredValue(
                $xpath,
                '/n:NFSe/n:infNFSe/n:DPS/n:infDPS/n:serv/n:cServ/n:xDescServ'
            ),
        );
    }

    private function loadDocument(string $xml): DOMDocument
    {
        $previousState = libxml_use_internal_errors(true);

        try {
            $document = new DOMDocument;
            $loaded = $document->loadXML($xml, LIBXML_NONET | LIBXML_NOBLANKS);
            libxml_clear_errors();
        } finally {
            libxml_use_internal_errors($previousState);
        }

        if (! $loaded) {
            throw new InvalidInvoiceXml('O arquivo não é um XML válido.');
        }

        return $document;
    }

    private function validateSchema(DOMDocument $document, string $version): void
    {
        $schemaPath = sprintf('%s/%s/NFSe_v%s.xsd', $this->schemaDirectory, $version, $version);

        if (! is_file($schemaPath)) {
            throw new InvalidInvoiceXml('O esquema da NFS-e não está disponível no servidor.');
        }

        $previousState = libxml_use_internal_errors(true);

        try {
            $valid = $document->schemaValidate($schemaPath, LIBXML_NONET);
            libxml_clear_errors();
        } finally {
            libxml_use_internal_errors($previousState);
        }

        if (! $valid) {
            throw new InvalidInvoiceXml('O XML não segue o esquema oficial da NFS-e nacional.');
        }
    }

    private function requiredValue(DOMXPath $xpath, string $expression): string
    {
        $value = $this->optionalValue($xpath, $expression);

        if ($value === null) {
            throw new InvalidInvoiceXml('O XML não contém todos os dados fiscais obrigatórios.');
        }

        return $value;
    }

    private function optionalValue(DOMXPath $xpath, string $expression): ?string
    {
        $node = $xpath->query($expression)?->item(0);

        if ($node === null) {
            return null;
        }

        $value = trim($node->nodeValue ?? '');

        return $value === '' ? null : $value;
    }

    private function normalizeAmount(string $amount): string
    {
        if (preg_match('/^(0|[1-9][0-9]{0,14})(?:\.[0-9]{2})?$/', $amount) !== 1) {
            throw new InvalidInvoiceXml('O XML contém um valor monetário inválido.');
        }

        return str_contains($amount, '.') ? $amount : $amount.'.00';
    }

    private function issueDate(string $dateTime): string
    {
        try {
            return (new DateTimeImmutable($dateTime))
                ->setTimezone(new DateTimeZone('America/Sao_Paulo'))
                ->format('Y-m-d');
        } catch (Throwable) {
            throw new InvalidInvoiceXml('A data de emissão da NFS-e é inválida.');
        }
    }
}
