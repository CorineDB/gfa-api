<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class DecaissementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'montant'=> 10000000,
            'date' => date('y-m-d'),
            'type' => 'pret'
        ];
    }
}
