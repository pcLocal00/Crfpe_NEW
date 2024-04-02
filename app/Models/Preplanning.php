<?php

namespace App\Models;
use App\Models\Formation;
use App\Models\Action;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Preplanning extends Model
{
    use HasFactory;
    protected $fillable = ['title','state','Start_date','PF_id','AF_target_id'];

    protected $table = 'af_preplannings';

    protected $casts = [
        'Start_date' => 'datetime',
     ];

    public function formation()
    {
        return $this->belongsTo(Formation::class, 'PF_id');
    }

    public function action()
    {
        return $this->belongsTo(Action::class, 'AF_target_id');
    }
}
