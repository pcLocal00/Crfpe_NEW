<?php

namespace App\Models;

use App\Models\User;
use App\Models\Profil;
use App\Models\Permission;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Role extends Model
{
    use HasFactory;

    protected $table = 'par_usr_roles';

    public function profil()
    {
        return $this->belongsTo(Profil::class);
    }
    public function users()
    {
        return $this->belongsToMany(User::class, 'par_usr_role_user');
    }
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'par_usr_permission_role');
    }
}
