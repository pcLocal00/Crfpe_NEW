<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;

class Estimate extends Model
{
    use HasFactory,SoftDeletes,LogsActivity;
    protected $table = 'dev_estimates';
    protected $guarded = [];  

    protected static $logName = 'estimate_log';
    public function entity()
    {
        return $this->belongsTo(Entitie::class, 'entitie_id');
    }
    public function contact()
    {
        return $this->belongsTo(Contact::class, 'contact_id');
    }
    public function af()
    {
        return $this->belongsTo(Action::class, 'af_id');
    }
}
