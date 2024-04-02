<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sessiondate extends Model
{
    use HasFactory,SoftDeletes;
    protected $table = 'af_sessiondates';
    public function session()
    {
        return $this->belongsTo(Session::class, 'session_id');
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class, 'sessiondate_id');
    }
}
