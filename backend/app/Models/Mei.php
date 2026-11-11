<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\MeiFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Mei extends Model
{
    /** @use HasFactory<MeiFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'mei';

    protected $fillable = [
        'cnpj',
        'mei_name',
        'opening_date',
        'ibge_municipality_code',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected function casts(): array
    {
        return [
            'opening_date' => 'date',
        ];
    }
}
