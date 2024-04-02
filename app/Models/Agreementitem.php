<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;

class Agreementitem extends Model
{
    use HasFactory,SoftDeletes,LogsActivity;
    protected $table = 'af_agreement_items';
    protected static $logName = 'agreement_item_log';

    protected $guarded = [];

    public function agreement()
    {
        return $this->belongsTo(Agreement::class, 'agreement_id');
    }
}
