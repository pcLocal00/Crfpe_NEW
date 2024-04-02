<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Attachment;

class Media extends Model
{
    use HasFactory;
    protected $table = 'ged_medias';
    protected $fillable = ['attachment_id','table_id','table_name'];


    public function attachment(){

        return $this->belongsTo(Attachment::class, 'attachment_id');
    }
}
