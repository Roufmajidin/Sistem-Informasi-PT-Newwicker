<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SpkScanLog extends Model
{
    protected $table = 'spk_scan_logs';

    protected $fillable = [
        'user_id',
        'spk_id',
        'detail_po_id',
        'sequence',
        'qty',
        'qr_raw',
        'scan_method',
        'status',
        'message',
        'scanned_at',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'spk_id' => 'integer',
        'detail_po_id' => 'integer',
        'sequence' => 'integer',
        'qty' => 'integer',
        'scanned_at' => 'datetime',
    ];

    public function spk(): BelongsTo
    {
        return $this->belongsTo(Spk::class, 'spk_id');
    }

    public function detailPo(): BelongsTo
    {
        return $this->belongsTo(DetailPo::class, 'detail_po_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopeSuccessful($query)
    {
        return $query->where('status', 'success');
    }
}