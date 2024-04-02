<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Refund extends Model
{
    use HasFactory,SoftDeletes,LogsActivity;
    protected $table = 'inv_refunds';

    protected static $logName = 'refunds_log';

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }
}
