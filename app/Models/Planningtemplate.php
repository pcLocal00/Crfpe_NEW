<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Planningtemplate extends Model
{
    use HasFactory,SoftDeletes;
    protected $table = 'par_planning_templates';
    public function periods()
    {
        return $this->belongsToMany(Templateperiod::class, 'par_template_periods');
    }
}
