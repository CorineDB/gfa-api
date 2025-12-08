<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class TacheFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'poids' => $this->faker->numberBetween($min = 1, $max = 100) ,
            'description' => $this->faker->sentence,
          

        ];
    }
}
