<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;

class Invoicepayment extends Model
{
    use HasFactory,SoftDeletes,LogsActivity;
    protected $table = 'inv_invoice_payments';

    protected static $logName = 'invoices_payments_log';
}
