<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModuleRoute extends Model
{
    protected $table = 'module_routes';

    protected $fillable = [
        'module_id',
        'name',
        'route_name',
        'url',
        'method',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function module()
    {
        return $this->belongsTo(Module::class);
    }
    
}