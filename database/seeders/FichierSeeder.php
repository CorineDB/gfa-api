<?php

namespace Database\Seeders;
use App\Models\Fichier;
use Illuminate\Database\Seeder;

class FichierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $fichier = Fichier::factory()->count(5)->create();

    }
}
