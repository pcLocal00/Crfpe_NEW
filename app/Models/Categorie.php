<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;

class Categorie extends Model
{
    use HasFactory,SoftDeletes,LogsActivity;

    protected $table = 'par_pf_categories';
    protected static $logName = 'categories_log';
    protected static $logAttributes = ['*'];
    protected static $logOnlyDirty = true;

    public function parent_categorie()
    {
        return $this->belongsTo(Categorie::class, 'categorie_id');
    }
    public function children()
    {
        return $this->hasMany(Categorie::class, 'categorie_id', 'id');
    }

    public function formations()
    {
        return $this->hasMany(Formation::class);
    }
}
