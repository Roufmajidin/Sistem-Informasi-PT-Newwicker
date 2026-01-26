<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kategori extends Model
{
   protected $table = 'kategori';
    protected $fillable = ['kategori'];

    public function checkpoints()
    {
        return $this->hasMany(Checkpoint::class);
    }
}
