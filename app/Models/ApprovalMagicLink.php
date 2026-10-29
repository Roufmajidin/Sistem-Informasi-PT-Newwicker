<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApprovalMagicLink extends Model
{
    protected $table = 'approval_magic_links';

    protected $fillable = [
        // Existing Payment Request
        'no_req',
        'user_id',
        'token_hash',
        'expires_at',
        'used_at',
        'created_by',
        'ip_address',
        'user_agent',

        // Magic Approval SPK
        'document_type',
        'spk_id',
        'approval_role',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
        'spk_id' => 'integer',
        'user_id' => 'integer',
        'created_by' => 'integer',
    ];

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function spk()
    {
        return $this->belongsTo(Spk::class, 'spk_id');
    }

    // ==========================================
    // QUERY SCOPES
    // ==========================================

    public function scopeForSpk($query, $spkId)
    {
        return $query
            ->where('document_type', 'spk')
            ->where('spk_id', $spkId);
    }

    public function scopeActive($query)
    {
        return $query
            ->whereNull('used_at')
            ->where('expires_at', '>', now());
    }

    // ==========================================
    // STATUS HELPERS
    // ==========================================

    public function isExpired(): bool
    {
        return $this->expires_at === null
            || $this->expires_at->isPast();
    }

    public function isUsed(): bool
    {
        return $this->used_at !== null;
    }

    public function isValid(): bool
    {
        return !$this->isUsed()
            && !$this->isExpired();
    }

    public function isSpk(): bool
    {
        return $this->document_type === 'spk';
    }
}
