<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailPo extends Model
{
    protected $table = 'detail_po';

    protected $fillable = [
        'po_id',
        'detail',
    ];

    protected $casts = [
        'detail' => 'array',
    ];

    public function po()
    {
        return $this->belongsTo(Po::class);
    }
}
