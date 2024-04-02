<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Group;
class Member extends Model
{
    use HasFactory,SoftDeletes;
    protected $table = 'af_members';

    const STOP_REASON_TXT = [
        "stop" => 'Exclusion',
        "suspend" => "Suspension",
        "cancel" => "Abondance",
    ];

    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class, 'enrollment_id');
    }
    public function contact()
    {
        return $this->belongsTo(Contact::class, 'contact_id');
    }
    public function group()
    {
        // return $this->belongsTo(Enrollment::class, 'group_id');
        return $this->belongsTo(Group::class, 'group_id');
    }

    protected $casts = [
        'effective_date' => 'date',
        'resumption_date' => 'date',
    ];
}
