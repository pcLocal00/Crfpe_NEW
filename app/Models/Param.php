<?php

namespace App\Models;

use App\Models\Formation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;

class Param extends Model
{
    use HasFactory,LogsActivity,SoftDeletes;

    protected $table = 'par_params';
    protected static $logAttributes = ['*'];

    public function formations()
    {
        return $this->belongsToMany(Formation::class, 'pf_formation_param');
    }
}
