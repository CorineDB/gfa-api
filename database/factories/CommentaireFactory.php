<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CommentaireFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'contenu' =>$this->faker->paragraph(1) ,
            'commentable_type' => $this->faker->randomElement(['App\Models\Activite', 'App\Models\Composante', 'App\Models\Tache', 'App\Models\Fichier']),
            'commentable_id'=>$this->faker-> numberBetween($min = 1, $max = 8) ,
            'userId' =>$this->faker-> numberBetween($min = 1, $max = 8),
        
        ];
    }
}
