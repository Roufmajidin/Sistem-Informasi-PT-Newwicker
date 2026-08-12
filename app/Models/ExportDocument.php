<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExportDocument extends Model
{
    protected $guarded = [];

    public function po()
    {
        return $this->belongsTo(Po::class);
    }

    public function exportIpl()
    {
        return $this->belongsTo(
            ExportIpl::class,
            'export_ipl_id'
        );
    }

    public function invoice()
    {
        return $this->belongsTo(
            ExportIpl::class,
            'invoice_id'
        );
    }

    public function packingList()
    {
        return $this->belongsTo(
            ExportIpl::class,
            'packing_list_id'
        );
    }

    public function creator()
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

    public function files()
    {
        return $this->hasMany(
            ExportDocumentFile::class
        );
    }
}