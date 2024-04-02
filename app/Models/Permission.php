<?php

namespace App\Models;

use App\Models\Role;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Permission extends Model
{
    use HasFactory;

    protected $table = 'par_usr_permissions';

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'par_usr_permission_role');
    }
}
