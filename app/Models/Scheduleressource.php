<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Scheduleressource extends Model
{
    use HasFactory,SoftDeletes;
    protected $table = 'af_scheduleressources';
    public function schedule()
    {
        return $this->belongsTo(Schedule::class, 'schedule_id');
    }
    public function ressource()
    {
        return $this->belongsTo(Ressource::class, 'ressource_id');
    }
}
