<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;

class Taxe extends Model
{
    use HasFactory,SoftDeletes,LogsActivity;
    protected $table = 'par_taxes';
    protected static $logName = 'taxe_log';
}
