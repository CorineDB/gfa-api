<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class MODFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'userId' => 9,
            'programmeId' => 1,
        ];
    }
}
