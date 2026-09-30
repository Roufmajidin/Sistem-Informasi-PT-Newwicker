<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExportArLegacy extends Model
{
    protected $table = 'export_ar_legacy';

    protected $fillable = [
        'nama_pelanggan', 'no_po', 'no_invoice', 'tanggal_invoice', 'tanggal_shipment',
        'no_pengajuan_peb', 'no_peb', 'fob_usd', 'fob_peb_usd', 'kurs_kemenkeu',
        'jumlah_container', 'deposit_date', 'deposit_usd', 'pelunasan_date',
        'pelunasan_usd', 'sisa_piutang_usd', 'keterangan', 'remark', 'created_by', 'm_cont',
    ];

    protected $casts = [
        'tanggal_invoice' => 'date',
        'tanggal_shipment' => 'date',
        'deposit_date' => 'date',
        'pelunasan_date' => 'date',
        'fob_usd' => 'decimal:2',
        'fob_peb_usd' => 'decimal:2',
        'kurs_kemenkeu' => 'decimal:2',
        'deposit_usd' => 'decimal:2',
        'pelunasan_usd' => 'decimal:2',
        'sisa_piutang_usd' => 'decimal:2',
        'jumlah_container' => 'integer',
        'created_by' => 'integer',
    ];
}
