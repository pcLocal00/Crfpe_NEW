<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ressource extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'res_ressources';

    public function parent_ressource()
    {
        return $this->belongsTo(Ressource::class, 'ressource_id');
    }

    public function children()
    {
        return $this->hasMany(Ressource::class, 'ressource_id', 'id');
    }

    public function groups()
    {
        return $this->hasMany(Group::class);
    }
}
