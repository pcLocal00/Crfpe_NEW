<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Schedule extends Model
{
    use HasFactory,SoftDeletes;
    protected $table = 'af_schedules';
    public function sessiondate()
    {
        return $this->belongsTo(Sessiondate::class, 'sessiondate_id');
    }

    public function scheduleressources()
    {
        return $this->hasMany(Scheduleressource::class, 'schedule_id');
    }

    public function schedulecontacts()
    {
        return $this->hasMany(Schedulecontact::class, 'schedule_id');
    }
}
