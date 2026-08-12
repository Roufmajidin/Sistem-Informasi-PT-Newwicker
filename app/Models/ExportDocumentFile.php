<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExportDocumentFile extends Model
{
    protected $guarded = [];

    public function exportDocument()
    {
        return $this->belongsTo(
            ExportDocument::class
        );
    }
}