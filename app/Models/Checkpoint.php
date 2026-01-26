<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Checkpoint extends Model
{
    //
    protected $table    = 'checkpoint';
    protected $fillable = ['name', 'kategori_id'];

    public function kategori()
    {
        return $this->belongsTo(Kategori::class);
    }
}
