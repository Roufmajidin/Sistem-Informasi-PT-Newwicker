<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    use HasFactory;

    protected $table = 'vendors';

    protected $fillable = [
        'nama_vendor',
        'alamat',
        'nomor_rekening',
        'nama_rekening',
        'bank',
        'npwp',
        'vendor_type',
        'vendor_type2',
        'uniq'
    ];
}