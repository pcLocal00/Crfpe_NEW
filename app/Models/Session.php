<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;

class Session extends Model
{
    use HasFactory,SoftDeletes,LogsActivity;
    protected $table = 'af_sessions';

    protected static $logName = 'af_session_log';
    protected static $logAttributes = ['*'];
    protected static $logOnlyDirty = true;

    public function af()
    {
        return $this->belongsTo(Action::class, 'af_id');
    }
    public function sessiondates()
    {
        return $this->hasMany(Sessiondate::class, 'session_id', 'id');
    }
}
