<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Po extends Model
{
    protected $table = 'po';

    protected $fillable = [
        'order_no',
        'company_name',
        'country',
        'release_date',
        'shipment_date',
        'act_ship',
        'value',
        'cont_numb',
        'do_released',
        'remark',
        'category',
        'packing',
        'contact_person',
        'detail',
    ];

    public function details()
    {
        return $this->hasMany(DetailPo::class);
    }
    public function spks()
    {
        return $this->hasMany(Spk::class, 'po_id');
    }

    public function detailPos()
    {
        return $this->hasMany(DetailPo::class, 'po_id');
    }
    public function detailPo()
    {
        return $this->belongsTo(
            DetailPo::class,
            'detail_po_id'
        );
    }
}
