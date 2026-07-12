<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SignatureSpk extends Model
{
    protected $fillable = [
        'spk_id',
        'supplier_id',
        'made_by',
        'checked_by',
        'approved_by',
        'made_at',
        'checked_at',
        'approved_at',
        'made_remark',
        'checked_remark',
        'approved_remark',
    ];

    protected $casts = [
        'made_at'     => 'datetime',
        'checked_at'  => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function spk()
    {
        return $this->belongsTo(Spk::class);
    }

    public function maker()
    {
        return $this->belongsTo(User::class,'made_by');
    }

    public function checker()
    {
        return $this->belongsTo(User::class,'checked_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class,'approved_by');
    }
     public function supplier()
    {
        return $this->belongsTo(
            Supplier::class,
            'supplier_id'
        );
    }

    public function madeBy()
    {
        return $this->belongsTo(
            User::class,
            'made_by'
        );
    }

    public function checkedBy()
    {
        return $this->belongsTo(
            User::class,
            'checked_by'
        );
    }

    public function approvedBy()
    {
        return $this->belongsTo(
            User::class,
            'approved_by'
        );
    }
}