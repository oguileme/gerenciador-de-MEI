<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MeiAnnualLimit extends Model
{
    protected $fillable = ['mei_id', 'year', 'amount'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['year' => 'integer', 'amount' => 'decimal:2'];
    }
}
