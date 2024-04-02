<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\Traits\LogsActivity;

class Entitie extends Model
{
    use HasFactory,SoftDeletes,LogsActivity;
    protected $table = 'en_entities';
    protected static $logName = 'entities_log';
    protected $guarded = [];
    
    public function contacts()
    {
        return $this->hasMany(Contact::class);
    }
    public function adresses()
    {
        return $this->hasMany(Adresse::class);
    }
    public function parent()
    {
        return $this->belongsTo(Entitie::class, 'entitie_id');
    }
    public function children()
    {
        return $this->hasMany(Entitie::class, 'entitie_id', 'id');
    }
}
