<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use App\Models\Profil;
use App\Models\Categorie;
use App\Models\Formation;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();
        //Profil::factory(4)->create();
        //Role::factory(3)->create();
        /* $roles = Role::all();
        User::all()->each(function ($user) use ($roles) { 
            $user->roles()->attach($roles->random(rand(1, 3))->pluck('id')->toArray()); 
        }); */

        //Role::factory(2)->create();
        //Categorie::factory(2)->create();
        Formation::factory(20)->create();

    }
}
