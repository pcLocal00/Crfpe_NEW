<?php

namespace App\Models;

use App\Models\Attachment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Attachmentlog extends Model
{
    use HasFactory;
    protected $table = 'ged_attachment_logs';
    //protected $fillable = ['attachment_id','log_desc'];
    
    public function file(){
        return $this->belongsTo(Attachment::class, 'attachment_id');
    }
}
