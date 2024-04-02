<?php

namespace Database\Factories;

use App\Models\Profil;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProfilFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Profil::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'code' => $this->faker->name,
            'name' => $this->faker->name,
        ];
    }
}
