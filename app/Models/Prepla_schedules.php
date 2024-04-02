<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prepla_schedules extends Model
{
    use HasFactory;
    protected $fillable = ['Pp_id','title','date_start','start_hour','end_hour','sequence_number','sequence_total','color','remarks','Pf_session'];

    protected $table = 'af_prepla_schedules';

    public function getRouteKeyName(){
        return 'title';
    }
    public function intervenants()
    {
        return $this->hasManyThrough(
            'App\Models\Contact',
            'App\Models\Prepla_scheduledate_intervenants',
            'Pp_schedule_id',
            'id',
            'id',
            'Contact_id'
        );
    }
}
