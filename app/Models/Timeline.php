<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Timeline extends Model
{
    use HasFactory;

    protected $table = 'timeline'; // nama tabel

    protected $fillable = ['isi', 'jenis']; // kolom yang bisa diisi massal

    // Jika ingin otomatis cast kolom JSON ke array saat diambil
    protected $casts = [
        'isi' => 'array',
    ];
    public function user()
{
    return $this->belongsTo(User::class, 'user_id');
}
}
