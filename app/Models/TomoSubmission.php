<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class TomoSubmission extends Model {
    protected $table = 'tomo_submissions';
    protected $fillable = ['po_code','detail_po_id','article_code','item_name','qty','image','notes','points_json','total','status','reviewed_at','review_notes'];
    protected $casts = ['points_json'=>'array','qty'=>'decimal:2','total'=>'decimal:2','reviewed_at'=>'datetime'];
}
