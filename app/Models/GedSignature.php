<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GedSignature extends Model
{
    use HasFactory;
    protected $table = 'ged_signatures';
    protected $guarded = [];

    public $timestamps = false;

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    public function agreement()
    {
        return $this->belongsTo(Agreement::class);
    }
    
    public function task()
    {
        return $this->belongsTo(Task::class);
    }
}
