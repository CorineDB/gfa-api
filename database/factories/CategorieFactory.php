<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CategorieFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'id' => $this->faker->unique()->numberBetween($min = 1, $max = 5),
            'nom' => $this->faker->word,
        ];
    }
}
