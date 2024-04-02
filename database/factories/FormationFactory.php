<?php

namespace Database\Factories;

use App\Models\Formation;
use Illuminate\Database\Eloquent\Factories\Factory;

class FormationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Formation::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'code' => $this->faker->name,
            'title' => $this->faker->sentence,
            'description' => $this->faker->paragraph($nbSentences = 3, $variableNbSentences = true),
            'max_availability' => $this->faker->randomDigit,
            'categorie_id' => 2,
        ];
    }
}
