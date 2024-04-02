<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;

class Funding extends Model
{
    use HasFactory,SoftDeletes,LogsActivity;
    protected $table = 'af_fundings';
    protected static $logName = 'funding_log';
    public function entity()
    {
        return $this->belongsTo(Entitie::class, 'entitie_id');
    }
    public function agreement()
    {
        return $this->belongsTo(Agreement::class, 'agreement_id');
    }
}
