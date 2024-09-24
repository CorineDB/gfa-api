<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ActiviteUserFactory extends Factory
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
            'type' => $this->faker->name(),
            'activiteId'=> $this->faker->numberBetween($min = 1, $max =25),
            'userId'=> $this->faker->numberBetween($min = 1, $max =16)
        ];
    }
}
