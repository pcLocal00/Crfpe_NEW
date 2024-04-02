<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Price extends Model
{
    use HasFactory,SoftDeletes;
    protected $table = 'pf_prices';
    public function formations()
    {
        return $this->belongsToMany(Formation::class, 'pf_rel_price');
    }
    public function actions()
    {
        return $this->belongsToMany(Action::class, 'af_rel_price', 'af_id', 'price_id');
    }
}
