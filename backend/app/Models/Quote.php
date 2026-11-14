<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\QuoteStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Proposta comercial e fotografia dos dados apresentados ao cliente. */
class Quote extends Model
{
    protected $fillable = [
        'mei_id',
        'issuer_name',
        'recipient_name',
        'issue_date',
        'status',
        'total_amount',
    ];

    /** @return BelongsTo<Mei, $this> */
    public function mei(): BelongsTo
    {
        return $this->belongsTo(Mei::class);
    }

    /** @return HasMany<QuoteItem, $this> */
    public function items(): HasMany
    {
        return $this->hasMany(QuoteItem::class);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'issue_date' => 'date',
            'status' => QuoteStatus::class,
            'total_amount' => 'decimal:2',
        ];
    }
}
