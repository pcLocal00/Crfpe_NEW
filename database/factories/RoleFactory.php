<?php

namespace Database\Factories;

use App\Models\Role;
use App\Models\Profil;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoleFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Role::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        /* $profiles=Profil::pluck('id')->toArray();
        return [
            'code' => $this->faker->name,
            'name' => $this->faker->name,
            'profil_id' => $this->faker->randomElement($profiles),
        ]; */
        $profil=Profil::find(4);
        return [
            'code' => $this->faker->name,
            'name' => $this->faker->name,
            'profil_id' => $profil->id,
        ];
    }
}
