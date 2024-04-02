<?php

namespace App\Models;

use App\Models\Param;
use App\Models\Sheet;
use App\Models\Action;
use App\Models\Categorie;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;

class Formation extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    const EVALUATION_MODES = [
        'PRESENTIEL' => 'Présentiel',
        'EP_ECRIT' => 'Epreuve écrite',
        'EP_ORAL' => 'Epreuve Oral',
        'EXPOSE' => 'Exposé',
        'DOSSIER' => 'Dossier',
        'FORMATIF' => 'Formatif',
    ];

    protected $table = 'pf_formations';
    protected static $logName = 'formations_log';
    protected static $logAttributes = ['*'];
    protected static $logOnlyDirty = true;

    public function categorie()
    {
        return $this->belongsTo(Categorie::class);
    }
    public function params()
    {
        return $this->belongsToMany(Param::class, 'pf_formation_param');
    }
    public function sheets()
    {
        return $this->belongsToMany(Sheet::class, 'pf_sheets');
    }
    public function actions()
    {
        return $this->belongsToMany(Action::class, 'af_actions');
    }
    public function prices()
    {
        return $this->belongsToMany(Price::class, 'pf_rel_price');
    }
}
