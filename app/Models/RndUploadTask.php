<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class RndUploadTask extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['id','user_id','folder_id','original_name','mime_type','file_size','local_path','status','error_message','google_drive_file_id','archive_file_id'];
}
