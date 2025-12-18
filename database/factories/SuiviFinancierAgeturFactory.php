<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class SuiviFinancierAgeturFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'trimestre' => $this->faker->numberBetween(1, 4),
            'annee' => $this->faker->numberBetween($min = 2016, $max = 2022),
            'decaissement' =>$this->faker->numberBetween($min = 200000, $max = 1000000000),
            'taux' => $this->faker->numberBetween($min = 1, $max = 100),
            'commentaire' => $this->faker->paragraph(1),
            'suivitable_type' => $this->faker->randomElement(['App\Models\Activite', 'App\Models\Composante', 'App\Models\Tache']),
            'suivitable_id' => $this->faker->numberBetween($min = 1, $max = 4),
        ];
    }
}
