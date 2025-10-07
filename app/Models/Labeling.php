<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Labeling extends Model
{
    use HasFactory;

    protected $fillable = [
        'description',
        'labels',
        'jadwal_container',
        'status_rouf',
        'status_yogi',
    ];

    protected $casts = [
        'labels' => 'array',
        'jadwal_container' => 'date',
    ];
}
