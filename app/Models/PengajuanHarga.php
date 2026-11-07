<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PengajuanHarga extends Model
{
    protected $table = 'pengajuan_harga';
    protected $fillable = [
        'po_id', 'article_nr', 'item_name', 'wdh', 'image',
        'tgl_pengajuan', 'user_id', 'remark', 'remark_2', 'sub_name',
    ];
    protected function casts(): array
    {
        return [
            'tgl_pengajuan' => 'date',
            'approved_date' => 'datetime',
            'approve_negosiasi_harga' => 'decimal:2',
        ];
    }
    public function kategoriBahans(): HasMany { return $this->hasMany(KategoriBahan::class, 'pengajuan_harga_id'); }
    public function details(): HasMany { return $this->hasMany(PengajuanHargaDetail::class, 'pengajuan_harga_id'); }
    public function user(): BelongsTo { return $this->belongsTo(User::class, 'user_id'); }
    public function po(): BelongsTo { return $this->belongsTo(Po::class, 'po_id'); }
}
