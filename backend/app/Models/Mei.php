<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mei extends Model
{
    //
    protected $table = 'mei';
    protected $fillable = [
        'cnpj',
        'mei_name',
        'opening_date',
        'ibge_municipality_code',
        'user_id'
    ];

    function user()
    {
        return $this->belongsTo(User::class);
    }
}
