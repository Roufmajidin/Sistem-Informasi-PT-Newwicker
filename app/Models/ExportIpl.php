<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExportIpl extends Model
{
    protected $fillable = [
        'invoice_no',
        'sales_order',
        'released',
        'release_date',

        'buyer',
        'buyer_address',

        'customer_code',
        'customer_po_no',

        'container_type',
        'container_no',
        'seal_no',

        'vessel_name',

        'port_loading',
        'port_discharge',

        'commodity',

        'fumigation',

        'etd',
        'eta',

        'created_by',
    ];

    protected $casts = [
        'etd' => 'date',
        'eta' => 'date',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function pos()
    {
        return $this->hasMany(ExportIplPo::class);
    }

    public function items()
    {
        return $this->hasMany(ExportIplItem::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function exportDocumentsInvoice()
{
    return $this->hasMany(
        ExportDocument::class,
        'invoice_id'
    );
}

public function exportDocumentsPacking()
{
    return $this->hasMany(
        ExportDocument::class,
        'packing_list_id'
    );
}
}
