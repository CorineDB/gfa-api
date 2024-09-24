<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class IndicateurUniteeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'indicateurId' =>$this->faker->numberBetween($min = 1, $max = 7),
            'uniteeId' => $this->faker->numberBetween($min = 1, $max = 4),
        ];
    }
}
