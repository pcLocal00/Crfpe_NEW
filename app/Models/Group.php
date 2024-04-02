<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Contact;
use Illuminate\Database\Eloquent\SoftDeletes;

class Group extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'af_groups';

    public function action()
    {
        return $this->belongsTo(Action::class, 'af_id');
    }

    public function members()
    {
        return $this->hasMany(Member::class, 'group_id', 'id');
    }

    public function referance()
    {
        return $this->belongsTo(Contact::class, 'ref_contact_id');
    }
}
