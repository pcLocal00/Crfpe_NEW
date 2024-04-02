<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    use HasFactory;
    protected $table = 'ged_attachments';
    // protected $fillable = ['name','path'];
    protected $guarded = [];

    public $timestamps = true;
}
