<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ComposanteFactory extends Factory
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
            'pret'=> $this->faker->numberBetween($min = 0, $max = 6000000),
            'budgetNational'=> 0,
            'description' => $this->faker->sentence,
            'composanteId' => 0
        ];
    }
}
