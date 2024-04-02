<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Timestructure extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pf_timestructures';

    public function model()
    {
        return $this->belongsTo(Timestructurecategory::class,'category_id');
    }

    public function children()
    {
        return $this->hasMany(Timestructure::class, 'parent_id', 'id');
    }
}
