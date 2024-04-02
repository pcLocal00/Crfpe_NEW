<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AfSchedulegroup extends Model
{
    use HasFactory;
    protected $fillable = ['schedule_id','group_id','regroup_id'];

    protected $table = 'af_schedulegroups';
}
