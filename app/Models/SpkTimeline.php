<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpkTimeline extends Model
{
    //
    protected $table    = 'spk_timeline';
    protected $fillable = ['spk_id', 'data'];
    protected $casts    = [
        'data' => 'array',
    ];
}
