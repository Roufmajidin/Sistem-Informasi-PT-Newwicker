<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Spk extends Model
{
    //
    protected $table    = 'spk';
    protected $fillable = ['po_id', 'detail_po_id', 'data', 'created_by', 'status'];
    protected $casts    = [
        'data' => 'array',
    ];
    public function po()
    {
        return $this->belongsTo(Po::class, 'po_id');
    }

    public function detailPo()
    {
        return $this->belongsTo(DetailPo::class, 'detail_po_id');
    }
    public function paymentRequests()
    {
        return $this->hasMany(
            PaymentRequest::class
        );
    }
    public function inspectSchedules()
    {
        return $this->hasMany(
            InspectSchedule::class,
            'spk_id'
        );
    }
    // apptoval signature spk
        public function madeBy()
    {
        return $this->belongsTo(User::class, 'made_by');
    }

    public function checkedBy()
    {
        return $this->belongsTo(User::class, 'checked_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

}
