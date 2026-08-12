<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    protected $table = 'modules';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
    public function routes()
    {
        return $this->hasMany(ModuleRoute::class);
    }
    public function activityLogs()
{
    return $this->hasMany(ActivityLog::class);
}
}