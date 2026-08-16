<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductSpecification extends Model
{
    use HasFactory;

    protected $table = 'product_specifications';

    protected $fillable = [
        'name',
        'article_code',
        'data',
        'created_by',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    /**
     * User yang membuat spesifikasi.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}