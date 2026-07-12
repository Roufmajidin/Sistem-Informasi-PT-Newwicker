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
        'checked_by_2',
        'approved_by',
        'made_at',
        'checked_at',
        'checked_at_2',
        'approved_at',
        'made_remark',
        'checked_remark',
        'checked_2_remark',
        'approved_remark',
    ];

    protected $casts = [
        'made_at'     => 'datetime',
        'checked_at'  => 'datetime',
        'approved_at' => 'datetime',
        'checked_at_2' => 'datetime',
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
     public function checker2()
    {
        return $this->belongsTo(User::class,'checked_by_2');
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
     public function checkedBy2()
    {
        return $this->belongsTo(
            User::class,
            'checked_by_2'
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
