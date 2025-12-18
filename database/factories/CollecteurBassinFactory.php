<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class SiteFactory extends Factory
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
            'travaux' => $this->faker->sentence,
            'estimation'=> $this->faker->numberBetween($min = 100000, $max = 6000000),
            'engagement'=> $this->faker->numberBetween($min = 100000, $max = 6000000),
            'reference' => $this->faker->sentence,
            'attributaire' => $this->faker->word,
            'commentaire' => $this->faker->paragraph(1),
            'userId'=> $this->faker->numberBetween($min = 2, $max = 8),

        ];
    }
}
