<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Groupmentgroup extends Model
{
    use HasFactory;
    protected $table = 'af_groupment_group';
    public function group()
    {
        return $this->belongsTo(Group::class, 'group_id');
    }
    public function groupment()
    {
        return $this->belongsTo(Groupment::class, 'groupment_id');
    }
}
