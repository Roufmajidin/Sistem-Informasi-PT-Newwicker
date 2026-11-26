<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class ProgressReport extends Model {
    use SoftDeletes;
    protected $fillable = ['report_date','period_start','period_end','status','created_by','updated_by','published_by','published_at'];
    protected $casts = ['report_date'=>'date','period_start'=>'date','period_end'=>'date','published_at'=>'datetime'];
    public function details() { return $this->hasMany(ProgressReportDetail::class)->orderByDesc('is_urgent')->orderBy('deadline')->orderBy('id'); }
    public function creator() { return $this->belongsTo(User::class,'created_by'); }
    public function publisher() { return $this->belongsTo(User::class,'published_by'); }
    public function updater() { return $this->belongsTo(User::class,'updated_by'); }
}
