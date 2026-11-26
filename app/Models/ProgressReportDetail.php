<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class ProgressReportDetail extends Model {
    use SoftDeletes;
    protected $fillable = ['subject','status','is_urgent','problem_order_impact','action','pic_id','deadline','result_decision','created_by','updated_by'];
    protected $casts = ['is_urgent'=>'boolean','deadline'=>'date'];
    public function report() { return $this->belongsTo(ProgressReport::class,'progress_report_id'); }
    public function pic() { return $this->belongsTo(User::class,'pic_id'); }
    public function creator() { return $this->belongsTo(User::class,'created_by'); }
    public function updater() { return $this->belongsTo(User::class,'updated_by'); }
}
