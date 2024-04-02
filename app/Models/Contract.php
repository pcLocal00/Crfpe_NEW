<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contract extends Model
{
    use HasFactory,SoftDeletes;
    protected $table = 'en_contracts';
    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }
}
