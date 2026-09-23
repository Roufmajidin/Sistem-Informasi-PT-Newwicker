<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApprovalMagicLink extends Model
{
    protected $table = 'approval_magic_links';

    protected $fillable = [
        'no_req',
        'user_id',
        'token_hash',
        'expires_at',
        'used_at',
        'created_by',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
    ];
}