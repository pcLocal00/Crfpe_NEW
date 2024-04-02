<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Documentmodel extends Model
{
    use HasFactory,SoftDeletes;
    protected $table = 'par_document_models';
}
