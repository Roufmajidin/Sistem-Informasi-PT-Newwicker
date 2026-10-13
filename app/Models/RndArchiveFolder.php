<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RndArchiveFolder extends Model
{
    protected $table = 'rnd_archive_folders';

    protected $fillable = [
        'parent_id',
        'po_id',
        'detail_po_id',
        'item_key',
        'folder_name',
        'folder_type',
        'google_drive_folder_id',
        'created_by',
    ];

    /**
     * Folder induk.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(
            RndArchiveFolder::class,
            'parent_id'
        );
    }

    /**
     * Subfolder.
     */
    public function children(): HasMany
    {
        return $this->hasMany(
            RndArchiveFolder::class,
            'parent_id'
        );
    }

    /**
     * PO terkait.
     */
    public function po(): BelongsTo
    {
        return $this->belongsTo(
            Po::class,
            'po_id'
        );
    }

    /**
     * Detail PO terkait.
     */
    public function detailPo(): BelongsTo
    {
        return $this->belongsTo(
            DetailPo::class,
            'detail_po_id'
        );
    }

    /**
     * File yang tersimpan di folder ini.
     */
    public function files(): HasMany
    {
        return $this->hasMany(
            RndArchiveFile::class,
            'folder_id'
        );
    }

    /**
     * User pembuat folder.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }
}