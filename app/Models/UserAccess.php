<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAccess extends Model
{
    protected $table = 'user_access';

    protected $fillable = [
        'id_access_module',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function module()
    {
        return $this->belongsTo(AccessModule::class, 'id_access_module', 'id');
    }
}