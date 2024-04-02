<?php

namespace App\Models;

use App\Models\Param;
use App\Models\Formation;
use App\Models\Action;
use App\Models\Contact;
use App\Models\Entitie;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;

class Task extends Model
{
    use HasFactory,SoftDeletes,LogsActivity;

    protected $table = 'tasks';
    protected static $logAttributes = ['*'];
    
    protected $guarded = [];

    public function af()
    {
        return $this->belongsTo(Action::class, 'af_id');
    }

    public function apporteur()
    {
        return $this->belongsTo(Contact::class, 'apporteur_id');
    }

    // public function callbackmode()
    // {
    //     return $this->belongsTo(Param::class, 'callback_mode_id');
    // }

    public function contact()
    {
        return $this->belongsTo(Contact::class, 'contact_id');
    }

    public function entitie()
    {
        return $this->belongsTo(Entitie::class, 'entite_id');
    }

    public function atat()
    {
        return $this->belongsTo(Param::class, 'etat_id');
    }

    public function pf()
    {
        return $this->belongsTo(Formation::class, 'pf_id');
    }

    public function reponsemode()
    {
        return $this->belongsTo(Param::class, 'reponse_mode_id');
    }

    public function responsable()
    {
        return $this->belongsTo(Contact::class, 'responsable_id');
    }

    public function source()
    {
        return $this->belongsTo(Param::class, 'source_id');
    }

    public function taskparent()
    {
        return $this->belongsTo(Task::class, 'task_parent_id');
    }

    
    public function type()
    {
        return $this->belongsTo(Param::class, 'type_id');
    }
    
    public function devis()
    {
        return $this->hasOne(Devis::class, 'task_id');
    }
}
