<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;
    protected $table='documents';
    protected $fillable = [
        'af_id',
        'task_id',
        'contact_id',
        'montant_ht',
        'montant_ttc',
        'type',
        'tva',
        'path',
        'state'
    ];
    public function action()
    {
        return $this->belongsTo(Action::class,'af_id');
    }
    public function task()
    {
        return $this->belongsTo(Task::class,'task_id');
    }
}
