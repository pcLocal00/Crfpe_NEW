<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;

class Agreement extends Model
{
    use HasFactory,SoftDeletes,LogsActivity;
    protected $table = 'af_agreements';
    protected static $logName = 'agreements_log';

    protected $guarded = [];

    public function af()
    {
        return $this->belongsTo(Action::class, 'af_id');
    }
    public function entity()
    {
        return $this->belongsTo(Entitie::class, 'entitie_id');
    }
    public function contact()
    {
        return $this->belongsTo(Contact::class, 'contact_id');
    }
    public function estimate()
    {
        return $this->belongsTo(Estimate::class, 'estimate_id');
    }
}
