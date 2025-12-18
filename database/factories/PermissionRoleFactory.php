<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PermissionRoleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'permissionId' => $this->faker->numberBetween(1, 16),
            'roleId' => $this->faker->numberBetween(2, 16)
        ];
    }
}
