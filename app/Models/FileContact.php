<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Attachment;
class FileContact extends Model
{
    use HasFactory;
    protected $table = 'ged_attachment_contacts';
    protected $fillable = ['file_id','contact_id','state', 'line_info', 'suggested_contacts'];


    public function file(){
        return $this->belongsTo(Attachment::class, 'attachment_id');
    }
}
