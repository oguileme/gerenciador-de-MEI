<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    //
    protected $table = 'users';
    protected $fillable = [
        'name',
        'email',
        'password',
        'birth_date'
    ];

    function mei()
    {
        return $this->hasOne(Mei::class);
    }
}
