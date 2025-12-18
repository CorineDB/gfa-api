<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class IndicateurFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'nom' => $this->faker->word, 
            'description' => $this->faker->paragraph(1),
            'anneeDeBase' => $this->faker->numberBetween(2016, 2024),
            'valeurDeBase'=> $this->faker->numberBetween(100000, 100000000),
            'frequence' => $this->faker->randomElement(['Trimestrielle', 'Annuelle']),
            'source' => $this->faker->sentence ,
            'responsable' => $this->faker->word,
            'definition' =>$this->faker->sentence ,
            'userId' => $this->faker->numberBetween($min = 2, $max = 8),
            'categorieId' => $this->faker->numberBetween($min = 1, $max = 5)
        ];
    }
}
