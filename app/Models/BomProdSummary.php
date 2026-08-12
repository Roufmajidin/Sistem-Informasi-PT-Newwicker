<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BomProdSummary extends Model
{
  protected $table = 'bom_summaries_prod';

  protected $guarded = [];

    public function bom()
    {
        return $this->belongsTo(BomProd::class);
    }
}
