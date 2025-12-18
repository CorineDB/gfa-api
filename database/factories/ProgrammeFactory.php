<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ProgrammeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'nom'  => 'PAPC',
            'description'  => $this->faker->paragraph(1),
            'debut' => '2018-01-01',
            'fin' => '2025-12-01',
            'code' => '2.4.1',
            'budgetNational'=> 9000000
        ];
    }
}
