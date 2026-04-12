<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CadModel extends Model
{
    //
    protected $table = 'cads';
    protected $fillable = [
        'article_code',
        'file_path',
        'version',
        'uploaded_by',
        'status',
        'approve_by',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
