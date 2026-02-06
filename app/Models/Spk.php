<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Spk extends Model
{
    //
    protected $table    = 'spk';
    protected $fillable = ['po_id', 'detail_po_id', 'data', 'created_by'];
    protected $casts    = [
        'data' => 'array',
    ];
}
