<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PermissionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $permission = $this->faker->word;
        return [
            'nom' => $permission,
            'description' => $this->faker->paragraph(1),
            'slug' => $permission,
        ];
    }
}
