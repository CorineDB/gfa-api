<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class MaitriseOeuvreFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'id' => $this->faker->numberBetween($min = 1, $max = 7),
            'nom' => $this->faker->word,
            'estimation' => $this->faker->numberBetween($min = 6000000, $max = 6000000000),
            'engagement' => $this->faker->numberBetween($min = 6000000, $max = 6000000000),
            'reference'=> $this->faker->sentence ,
            'attributaire' => $this->faker->word,
            'commentaire' =>$this->faker->paragraph(1),
            'userId' => $this->faker->numberBetween($min = 2, $max = 8),
            'maitriseId' => 0
        ];
    }
}
