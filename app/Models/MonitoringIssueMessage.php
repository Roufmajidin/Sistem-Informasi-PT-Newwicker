<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class MonitoringIssueMessage extends Model {
 use SoftDeletes;
 protected $table='monitoring_issue_messages';
 protected $fillable=['monitoring_issue_id','user_id','parent_id','message','attachment_path','attachment_name','edited_at'];
 protected $casts=['edited_at'=>'datetime'];
 public function issue():BelongsTo{return $this->belongsTo(MonitoringIssue::class,'monitoring_issue_id');}
 public function sender():BelongsTo{return $this->belongsTo(User::class,'user_id');}
 public function parent():BelongsTo{return $this->belongsTo(self::class,'parent_id');}
 public function replies():HasMany{return $this->hasMany(self::class,'parent_id');}
 public function reads():HasMany{return $this->hasMany(MonitoringIssueMessageRead::class,'message_id');}
}
