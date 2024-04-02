<?php

namespace App\Models;

use App\Models\Param;
use App\Models\Formation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;

class Sheet extends Model
{
    use HasFactory,SoftDeletes,LogsActivity;

    protected $table = 'pf_sheets';
    protected static $logAttributes = ['*'];

    public function formation()
    {
        return $this->belongsTo(Formation::class, 'formation_id');
    }
    //Etat de la fiche technique
    public function state()
    {
        return $this->belongsTo(Param::class, 'param_id');
    }
    public function sheetparams()
    {
        return $this->belongsToMany(Param::class, 'pf_sheet_param');
    }
}
