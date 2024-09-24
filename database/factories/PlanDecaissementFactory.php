<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PlanDecaissementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'annee' => 2022 ,
            'budgetNational'=> 0,
            'pret'=> $this->faker->numberBetween($min = 1000000, $max = 6000000),
        ];
    }
}
