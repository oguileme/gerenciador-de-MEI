<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\InvoiceStatus;
use App\Enums\InvoiceType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    protected $fillable = [
        'mei_id',
        'type',
        'status',
        'access_key',
        'invoice_number',
        'customer_name',
        'service_amount',
        'net_amount',
        'issue_date',
        'competence_date',
        'description',
        'xml_path',
        'xml_sha256',
    ];

    /**
     * @return BelongsTo<Mei, $this>
     */
    public function mei(): BelongsTo
    {
        return $this->belongsTo(Mei::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => InvoiceType::class,
            'status' => InvoiceStatus::class,
            'service_amount' => 'decimal:2',
            'net_amount' => 'decimal:2',
            'issue_date' => 'date',
            'competence_date' => 'date',
        ];
    }
}
