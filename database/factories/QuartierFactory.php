<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class QuartierFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'nom' => $this->faker->word ,
            'arrondissementId' => $this->faker->numberBetween($min = 1, $max =12)
        ];
    }
}
