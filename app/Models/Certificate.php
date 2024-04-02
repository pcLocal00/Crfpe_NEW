<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;

class Certificate extends Model
{
    use HasFactory,SoftDeletes,LogsActivity;
    protected $table = 'af_certificates';
    protected static $logName = 'certificates_log';
    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class, 'enrollment_id');
    }
    public function af()
    {
        return $this->belongsTo(Action::class, 'af_id');
    }
    public function contact()
    {
        return $this->belongsTo(Contact::class, 'contact_id');
    }
}
