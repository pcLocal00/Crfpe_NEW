<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CommitteeDecision extends Model
{
    use HasFactory;
    protected $table = 'af_committee_decisions';
    protected $fillable = [
        'comment',
        'member_id',
        'next_todo_comment',
        'send_transcript',
        'send_comment_mail',
    ];
}
