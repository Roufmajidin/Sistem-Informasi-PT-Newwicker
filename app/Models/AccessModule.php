<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccessModule extends Model
{
    protected $table = 'access_module';

    protected $fillable = [
        'name',
    ];

    public function userAccess()
    {
        return $this->hasMany(UserAccess::class, 'id_access_module', 'id');
    }

    public function users()
    {
        return $this->belongsToMany(
            User::class,
            'user_access',
            'id_access_module',
            'user_id'
        )->withTimestamps();
    }
}