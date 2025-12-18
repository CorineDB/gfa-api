<?php

namespace Database\Factories;
use App\Http\Models\Pta;
use App\Http\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjetFactory extends Factory
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
            'poids' => $this->faker->numberBetween($min = 1, $max = 100) ,
            'couleur' => '#FFF'. $this->faker->numberBetween($min = 1, $max = 9),
            'ville' => $this->faker->word,
            'pret'=> $this->faker->numberBetween($min = 0, $max = 6000000),
            'budgetNational'=> 0,
            'debut' => '2018-01-01',
            'fin' => '2025-12-31',
            'programmeId' => 1,
            'description' => $this->faker->sentence,
        ];
    }
}
