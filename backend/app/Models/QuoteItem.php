<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Linha de serviço com valores calculados pelo backend. */
class QuoteItem extends Model
{
    protected $fillable = [
        'description',
        'unit_price',
        'quantity',
        'subtotal',
    ];

    /** @return BelongsTo<Quote, $this> */
    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'quantity' => 'decimal:3',
            'subtotal' => 'decimal:2',
        ];
    }
}
