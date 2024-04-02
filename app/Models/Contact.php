<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;

class Contact extends Model
{
    use HasFactory, SoftDeletes,LogsActivity;

    protected $table = 'en_contacts';
    protected static $logName = 'contacts_log';
    protected $guarded = [];

    public function entitie()
    {
        return $this->belongsTo(Entitie::class);
    }

    public function user()
    {
        return $this->hasOne(User::class, 'contact_id');
    }

    public function groups()
    {
        return $this->belongsToMany(Group::class, 'af_group_contact');
    }
    public function devis()
    {
        return $this->hasOne(Devis::class, 'contact_id');
    }
    public function task()
    {
        return $this->hasMany(task::class, 'contact_id');
    }

}
