<?php

namespace Database\Seeders;
use App\Models\Commentaire;
use Illuminate\Database\Seeder;

class CommentaireSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $historique = Commentaire::factory()->count(5)->create();

    }
}
