<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuiTransfert extends Model
{
    use HasFactory;
    protected $fillable = ['af','prepla','pps_id','titre','date_s','heure_d','heure_f','couleur','pf_session','time_str','parent','contact','tarif','type_intervenant','regrp','grp','result'];

    protected $table = 'sui_transfert';
}
