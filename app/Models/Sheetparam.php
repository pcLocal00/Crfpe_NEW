<?php

namespace App\Models;

use App\Models\Param;
use App\Models\Sheet;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;

class Sheetparam extends Model
{
    use HasFactory,SoftDeletes,LogsActivity;

    protected $table = 'pf_sheet_param';

    protected static $logAttributes = ['*'];

    public function sheet()
    {
        return $this->belongsTo(Sheet::class, 'sheet_id');
    }
    public function param()
    {
        return $this->belongsTo(Param::class, 'param_id');
    }
}
