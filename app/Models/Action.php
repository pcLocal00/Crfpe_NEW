<?php

namespace App\Models;

use App\Models\Session;
use App\Models\Formation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\Traits\LogsActivity;

class Action extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'af_actions';

    protected static $logName = 'af_log';
    protected static $logAttributes = ['*'];
    protected static $logOnlyDirty = true;

    public function formation()
    {
        return $this->belongsTo(Formation::class, 'formation_id');
    }

    public function sessions()
    {
        return $this->hasMany(Session::class, 'af_id', 'id');
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class, 'af_id', 'id');
    }

    public function prices()
    {
        return $this->belongsToMany(Price::class, 'af_rel_price', 'af_id', 'price_id');
    }

    public function groups()
    {
        return $this->hasMany(Group::class);
    }
}
