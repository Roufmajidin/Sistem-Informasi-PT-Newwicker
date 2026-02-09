<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailPo extends Model
{
    protected $table = 'detail_po';

    protected $fillable = [
        'po_id',
        'detail',
        'updated_by',
    ];

    protected $casts = [
        'detail' => 'array',
        'updated_by' => 'array',
    ];

    public function po()
    {
        return $this->belongsTo(Po::class);
    }
    public function spks()
{
    return $this->hasMany(Spk::class, 'detail_po_id');
}

}
