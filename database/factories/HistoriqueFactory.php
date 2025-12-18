<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class HistoriqueFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $randIP = mt_rand(0, 255) . "." . mt_rand(0, 255) . "." . mt_rand(0, 255) . "." . mt_rand(0, 255);
        return [
            'description' => $this->faker->paragraph(1),
            'userAgent' => $this->faker->word,
            'ipAdresse' => $randIP,
            'userId' =>$this->faker->numberBetween($min = 1, $max = 15),
        ];
    }
}
