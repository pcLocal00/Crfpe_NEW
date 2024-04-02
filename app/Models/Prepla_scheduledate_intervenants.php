<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prepla_scheduledate_intervenants extends Model
{
    use HasFactory;
    
    protected $fillable = ['Pp_schedule_id','Contact_id','price','type'];

    protected $table = 'af_prepla_scheduledate_intervenants';
}
