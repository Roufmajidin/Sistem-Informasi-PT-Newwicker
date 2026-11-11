<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonitoringIssueComment extends Model
{
    protected $table = 'monitoring_issue_comments';

    protected $fillable = ['monitoring_issue_id', 'user_id', 'comments'];

    public function issue(): BelongsTo
    {
        return $this->belongsTo(MonitoringIssue::class, 'monitoring_issue_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
