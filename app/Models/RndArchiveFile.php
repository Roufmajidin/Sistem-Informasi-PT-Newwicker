<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RndArchiveFile extends Model
{
    protected $table = 'rnd_archive_files';

    protected $fillable = [
        'folder_id',
        'original_name',
        'google_drive_file_id',
        'mime_type',
        'file_size',
        'uploaded_by',
    ];

    protected $casts = [
        'file_size' => 'integer',
    ];

    /**
     * Folder tempat file disimpan.
     */
    public function folder(): BelongsTo
    {
        return $this->belongsTo(
            RndArchiveFolder::class,
            'folder_id'
        );
    }

    /**
     * User pengupload.
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'uploaded_by'
        );
    }
}