<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;

class Invoiceitem extends Model
{
    use HasFactory,SoftDeletes,LogsActivity;
    protected $table = 'inv_invoice_items';

    protected $guarded = [];

    protected static $logName = 'invoices_item_log';
    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }
}
