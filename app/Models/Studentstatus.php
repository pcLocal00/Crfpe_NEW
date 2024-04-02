<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class Studentstatus extends Model
{
    use HasFactory,LogsActivity;
    protected $table = 'af_student_status';

    protected static $logAttributes = ['*'];

    public function member()
    {
        return $this->belongsTo(Member::class, 'member_id');
    }
}
