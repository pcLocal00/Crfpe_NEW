<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prepla_scheduledate_groups extends Model
{
    use HasFactory;

    protected $fillable = ['Pp_schedule_id','Regroupement','Groupe'];

    protected $table = 'af_prepla_scheduledate_groups';
    
}
