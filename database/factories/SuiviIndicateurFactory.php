<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class SuiviIndicateurFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'annee' => $this->faker->numberBetween($min = 2016, $max = 2022),
            'trimestre' =>$this->faker->numberBetween(1, 4) ,
            'valeurCible' => [ 
                'key_1' => '10',
                'key_2' => '200',
                'key_2' => '5001',
                'key_2' => '2002',
            ],
            'valeurRealiser' => [ 
                'key_2' => '20',
                'key_2' => '201',
                'key_2' => '2022',
                'key_1' => '2000',

            ],
            'commentaire' => $this->faker->paragraph(1),

        ];
    }
}
