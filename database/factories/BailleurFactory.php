<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class BailleurFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'sigle' => $this->faker->name,
            'pays' => 'Fake'
        ];
    }
}
