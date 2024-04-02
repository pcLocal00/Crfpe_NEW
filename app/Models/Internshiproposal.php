<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Internshiproposal extends Model
{
    use HasFactory,SoftDeletes,LogsActivity;

    protected $table = 'af_internshiproposals';
    protected static $logName = 'internshiproposals_log';
    protected static $logAttributes = ['*'];
    public function af()
    {
        return $this->belongsTo(Action::class, 'af_id');
    }
    public function member()
    {
        return $this->belongsTo(Member::class, 'member_id');
    }
    public function entity()
    {
        return $this->belongsTo(Entitie::class, 'entity_id');
    }
    public function session()
    {
        return $this->belongsTo(Session::class, 'session_id');
    }
    public function representing_contact()
    {
        return $this->belongsTo(Contact::class, 'representing_contact_id');
    }
    public function trainer_referent_contact()
    {
        return $this->belongsTo(Contact::class, 'trainer_referent_contact_id');
    }
    public function internship_referent_contact()
    {
        return $this->belongsTo(Contact::class, 'internship_referent_contact_id');
    }
    public function adresse()
    {
        return $this->belongsTo(Adresse::class, 'adresse_id');
    }
}
