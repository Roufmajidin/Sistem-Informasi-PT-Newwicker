<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class MonitoringIssueMessageRead extends Model {
 public $timestamps=false;
 protected $table='monitoring_issue_message_reads';
 protected $fillable=['message_id','user_id','read_at'];
 protected $casts=['read_at'=>'datetime'];
}
