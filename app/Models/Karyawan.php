<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Karyawan extends Model
{
    //
    protected $guarded = [];

//     protected $fillable = [
//     'nama_lengkap',
//     'nik',
//     'jenis_kelamin',
//     'tempat',
//     'tanggal_lahir',
//     'alamat',
//     'status_perkawinan',
//     'divisi_id',
//     'status',
//     'lokasi',
//     'tanggal_join',
// ];
    public function divisi()
    {
        return $this->belongsTo(Divisi::class);
    }

}
