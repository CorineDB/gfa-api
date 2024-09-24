<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ActiviteQuartierFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            //'id' => $this->faker->unique()->numberBetween($min = 1, $max = 50),
            'activiteId'=> $this->faker->numberBetween($min = 1, $max =7),
            'quartierId'=> $this->faker->numberBetween($min = 1, $max =5)
        ];
    }
}
