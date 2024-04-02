<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;

class Fundingpayment extends Model
{
    use HasFactory,SoftDeletes,LogsActivity;
    protected $table = 'af_funding_payments';
    protected static $logName = 'funding_payment_log';
    public function funding()
    {
        return $this->belongsTo(Funding::class, 'funding_id');
    }
}
