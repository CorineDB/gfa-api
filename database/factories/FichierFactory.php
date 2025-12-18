<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class FichierFactory extends Factory
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
            'chemin' => './public/assets/files/'. $this->faker->word,
            'description' => $this->faker->paragraph(1),
            'fichiertable_type' => $this->faker->randomElement(['App\Models\Activite', 'App\Models\Composante', 'App\Models\Tache']) ,
            'fichiertable_id' =>$this->faker-> numberBetween($min = 1, $max = 4),
            'userId' => $this->faker->numberBetween($min = 1, $max = 16),
        ];
    }
}
