<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Moms extends Model
{
    protected $table = 'moms';

    protected $fillable = [
        'meeting_date',
        'start_month',
        'subject',
        'attendees',
        'discussions',
        'status',
        'write_by',
        'divisi'
    ];

    protected $casts = [
        'meeting_date' => 'date',
        'attendees' => 'array',
        'discussions' => 'array',
    ];
}
