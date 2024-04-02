<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;

class Enrollment extends Model
{
    use HasFactory,SoftDeletes,LogsActivity;
    protected $table = 'af_enrollments';

    protected static $logName = 'af_enrollment_log';
    protected static $logAttributes = ['*'];
    protected static $logOnlyDirty = true;

    public function action()
    {
        return $this->belongsTo(Action::class, 'af_id');
    }
    public function entity()
    {
        return $this->belongsTo(Entitie::class, 'entitie_id');
    }
    public function price()
    {
        return $this->belongsTo(Price::class, 'price_id');
    }
    public function members()
    {
        return $this->hasMany(Member::class, 'enrollment_id', 'id');
    }
}
