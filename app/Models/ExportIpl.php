<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExportIpl extends Model
{
    protected $fillable = [
        // EXISTING IPL
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
        'date',

        'final_destination',
        'final_destination_address',
        'eori',

        'incoterm',

        'country_of_origin',
        'rex',
        'igst_no',

        // =========================
        // SHIPPING INSTRUCTION
        // =========================
        'si_no',

        'shipping_forwarder',
        'attn',
        'booking_no',

        'peb_no',
        'peb_date',
        'kpbc_no',

        'lc_no',
        'freight',
        'contract_no',

        'notify_party',
        'connect_to',

        'bill_of_lading',

        'tare',
        'vgm',

        'location',
        'stuffing_date',
        'emkl',
    ];

    protected $casts = [
        'etd' => 'date',
        'eta' => 'date',
        'date' => 'date',
        'peb_date' => 'date',
        'stuffing_date' => 'date',

        'tare' => 'decimal:2',
        'vgm' => 'decimal:2',
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
