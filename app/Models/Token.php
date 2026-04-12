<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Token extends Model
{
    protected $table = 'tbl_tokens';

    protected $fillable = [
        'token',
        'name',
        'company_name',
        'email',
        'duration',
        'expired_at',
        'used'
    ];
}
