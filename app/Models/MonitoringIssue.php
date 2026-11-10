<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class MonitoringIssue extends Model
{
    protected $table = 'monitoring_issues';
    protected $fillable = ['user_id', 'problem', 'potential_impact', 'follow_director', 'status', 'tanggal_done', 'done_by', 'comments'];
    protected $casts = ['tanggal_done' => 'datetime'];
    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'done_by');
    }
    public function messages(): HasMany
    {
        return $this->hasMany(MonitoringIssueMessage::class, 'monitoring_issue_id');
    }
}
