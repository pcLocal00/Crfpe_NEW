<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Schedulecontact extends Model
{
    use HasFactory,SoftDeletes;
    protected $table = 'af_schedulecontacts';
    public function schedule()
    {
        return $this->belongsTo(Schedule::class, 'schedule_id');
    }
    public function member()
    {
        return $this->belongsTo(Member::class, 'member_id');
    }
    public function contract()
    {
        return $this->belongsTo(Contract::class, 'contract_id');
    }
}
