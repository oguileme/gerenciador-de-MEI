<?php

declare(strict_types=1);

namespace App\Domain\Invoices;

/**
 * Converte um XML não confiável em dados fiscais imutáveis, sem acessar banco ou disco.
 *
 * Implementações podem atender layouts diferentes sem alterar o fluxo de importação.
 */
interface InvoiceXmlParser
{
    /**
     * Valida o documento e devolve campos normalizados ou uma exceção de domínio.
     *
     * @throws Exceptions\InvalidInvoiceXml
     */
    public function parse(string $xml): ParsedInvoiceData;
}
