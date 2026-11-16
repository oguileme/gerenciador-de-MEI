<?php

declare(strict_types=1);

namespace App\Actions;

use App\Domain\Invoices\Exceptions\DuplicateInvoice;
use App\Domain\Invoices\Exceptions\InvalidInvoiceXml;
use App\Domain\Invoices\Exceptions\InvoiceStorageFailed;
use App\Domain\Invoices\InvoiceXmlParser;
use App\Enums\InvoiceStatus;
use App\Enums\InvoiceType;
use App\Models\Invoice;
use App\Models\Mei;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\QueryException;
use Illuminate\Filesystem\FilesystemManager;
use Throwable;

/**
 * Orquestra a importação sem misturar parsing, armazenamento e persistência.
 *
 * O parser trata o XML como entrada não confiável; esta ação confirma o vínculo com o
 * MEI, garante idempotência e preserva o documento original antes de criar a nota.
 */
final class ImportInvoiceFromXml
{
    public function __construct(
        private readonly InvoiceXmlParser $parser,
        private readonly FilesystemManager $filesystem,
        private readonly DatabaseManager $database,
    ) {}

    /**
     * Valida, identifica e persiste uma NFS-e vinculada ao MEI informado.
     *
     * @throws InvalidInvoiceXml quando conteúdo, tamanho ou vínculo fiscal são inválidos
     * @throws DuplicateInvoice quando hash ou chave de acesso já pertencem ao MEI
     * @throws InvoiceStorageFailed quando o documento original não pode ser preservado
     */
    public function execute(Mei $mei, string $xml): Invoice
    {
        $maximumBytes = (int) config('invoices.xml_max_kilobytes') * 1024;

        if (strlen($xml) > $maximumBytes) {
            throw new InvalidInvoiceXml('O XML excede o tamanho máximo permitido.');
        }

        $parsed = $this->parser->parse($xml);

        if ($parsed->issuerCnpj !== $mei->cnpj || $parsed->providerCnpj !== $mei->cnpj) {
            throw new InvalidInvoiceXml('O CNPJ da NFS-e não corresponde ao MEI selecionado.');
        }

        // O hash detecta o mesmo arquivo; a chave detecta o mesmo documento com bytes diferentes.
        $hash = hash('sha256', $xml);

        $duplicateExists = Invoice::query()
            ->where('mei_id', $mei->getKey())
            ->where(function ($query) use ($hash, $parsed): void {
                $query
                    ->where('xml_sha256', $hash)
                    ->orWhere('access_key', $parsed->accessKey);
            })
            ->exists();

        if ($duplicateExists) {
            throw new DuplicateInvoice;
        }

        $path = sprintf('xml/%s/%s.xml', $mei->getKey(), $hash);
        // Disco e banco não compartilham transação: um arquivo órfão é recuperável,
        // enquanto uma nota sem seu XML de origem não é.
        $this->storeXml($path, $xml);

        try {
            return $this->database->transaction(
                fn (): Invoice => $mei->invoices()->create([
                    'type' => InvoiceType::Nfse,
                    'status' => InvoiceStatus::Active,
                    'access_key' => $parsed->accessKey,
                    'invoice_number' => $parsed->invoiceNumber,
                    'customer_name' => $parsed->customerName,
                    'service_amount' => $parsed->serviceAmount,
                    'net_amount' => $parsed->netAmount,
                    'issue_date' => $parsed->issueDate,
                    'competence_date' => $parsed->competenceDate,
                    'description' => $parsed->description,
                    'xml_path' => $path,
                    'xml_sha256' => $hash,
                ])
            );
        } catch (QueryException $exception) {
            // O índice único fecha a corrida entre importações que passaram juntas
            // pela consulta anterior.
            if (in_array((string) $exception->getCode(), ['23000', '23505'], true)) {
                throw new DuplicateInvoice;
            }

            throw $exception;
        }
    }

    private function storeXml(string $path, string $xml): void
    {
        try {
            $disk = $this->filesystem->disk((string) config('invoices.xml_disk'));

            if (! $disk->exists($path) && ! $disk->put($path, $xml)) {
                throw new InvoiceStorageFailed;
            }
        } catch (InvoiceStorageFailed $exception) {
            throw $exception;
        } catch (Throwable) {
            throw new InvoiceStorageFailed;
        }
    }
}
