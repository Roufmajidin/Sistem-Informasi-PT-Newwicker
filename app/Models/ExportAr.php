<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExportAr extends Model
{
    protected $table = 'export_ars';

    protected $fillable = [
        'export_ipl_id',
        'tanggal_invoice',
        'jatuh_tempo',
        'fob_usd',
        'fob_peb_usd',
        'kurs_kemenkeu',
        'jumlah_rupiah',
        'jumlah_container',
        'no_pengajuan_peb',
        'no_peb',
        'status_ar',
        'keterangan',
        'remark',
        'created_by',
    ];

    protected $casts = [
        'tanggal_invoice' => 'date',
        'jatuh_tempo' => 'date',

        'fob_usd' => 'decimal:2',
        'fob_peb_usd' => 'decimal:2',
        'kurs_kemenkeu' => 'decimal:4',
        'jumlah_rupiah' => 'decimal:2',

        'jumlah_container' => 'integer',
    ];

    /**
     * AR berasal dari 1 Export IPL / Invoice.
     */
    public function exportIpl(): BelongsTo
    {
        return $this->belongsTo(
            ExportIpl::class,
            'export_ipl_id'
        );
    }

    /**
     * Satu AR dapat memiliki banyak pembayaran.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(
            ExportArPayment::class,
            'export_ar_id'
        );
    }

    /**
     * Total deposit.
     */
    public function getTotalDepositAttribute()
    {
        return $this->payments()
            ->where('payment_type', 'deposit')
            ->sum('amount');
    }

    /**
     * Total pelunasan.
     */
    public function getTotalPelunasanAttribute()
    {
        return $this->payments()
            ->where('payment_type', 'pelunasan')
            ->sum('amount');
    }

    /**
     * Total surcharge.
     */
    public function getTotalSurchargeAttribute()
    {
        return $this->payments()
            ->where('payment_type', 'surcharge')
            ->sum('amount');
    }

    /**
     * Total pembayaran.
     *
     * Surcharge TIDAK dihitung sebagai pembayaran.
     */
    public function getTotalDibayarAttribute()
    {
        return $this->total_deposit + $this->total_pelunasan;
    }

    /**
     * Sisa piutang.
     *
     * Jumlah Rp + Surcharge - Deposit - Pelunasan
     */
    public function getSisaPiutangAttribute()
    {
        return $this->jumlah_rupiah
            + $this->total_surcharge
            - $this->total_dibayar;
    }

    /**
     * Status otomatis berdasarkan pembayaran.
     */
    public function getStatusArCalculatedAttribute()
    {
        $sisa = $this->sisa_piutang;

        if ($sisa <= 0) {
            return 'lunas';
        }

        if ($this->total_dibayar <= 0) {
            return 'belum_dibayar';
        }

        if (
            $this->jatuh_tempo &&
            now()->startOfDay()->gt($this->jatuh_tempo)
        ) {
            return 'overdue';
        }

        return 'sebagian';
    }
}