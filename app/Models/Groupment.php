<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Groupment extends Model
{
    use HasFactory;
    protected $table = 'af_groupments';

    public function action()
    {
        return $this->belongsTo(Action::class, 'af_id');
    }
    
    public function groupmentsgroups()
    {
        return $this->hasMany(Groupmentgroup::class, 'group_id', 'id');
    }
}
