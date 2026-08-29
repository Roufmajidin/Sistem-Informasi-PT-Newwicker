<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MonitoringInvoice extends Model
{
    protected $table = 'monitoring_invoices';

    protected $fillable = [
        'nomor_invoice',
        'tanggal_invoice',
        'detail_bahan',
        'to_sub'
    ];

    protected $casts = [
        'tanggal_invoice' => 'date',
        'detail_bahan' => 'array',
    ];
}
