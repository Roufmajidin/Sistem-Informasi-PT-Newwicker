<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExportArPayment extends Model
{
    protected $table = 'export_ar_payments';

    protected $fillable = [
        'export_ar_id',
        'payment_type',
        'payment_date',
        'amount',
        'reference',
        'keterangan',
        'created_by',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
    ];

    /**
     * Payment milik satu AR.
     */
    public function exportAr(): BelongsTo
    {
        return $this->belongsTo(
            ExportAr::class,
            'export_ar_id'
        );
    }

    /**
     * Apakah payment berupa deposit?
     */
    public function isDeposit(): bool
    {
        return $this->payment_type === 'deposit';
    }

    /**
     * Apakah payment berupa pelunasan?
     */
    public function isPelunasan(): bool
    {
        return $this->payment_type === 'pelunasan';
    }

    /**
     * Apakah payment berupa surcharge?
     */
    public function isSurcharge(): bool
    {
        return $this->payment_type === 'surcharge';
    }
}