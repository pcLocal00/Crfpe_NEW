<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Templateperiod extends Model
{
    use HasFactory,SoftDeletes;
    protected $table = 'par_template_periods';
    public function template()
    {
        return $this->belongsTo(Planningtemplate::class, 'planning_template_id');
    }
}
