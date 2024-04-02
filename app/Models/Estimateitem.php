<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;

class Estimateitem extends Model
{
    use HasFactory,SoftDeletes,LogsActivity;
    protected $table = 'dev_estimate_items';
    protected static $logName = 'estimate_item_log';

    protected $guarded = [];
    
    public function estimate()
    {
        return $this->belongsTo(Estimate::class, 'estimate_id');
    }
}
