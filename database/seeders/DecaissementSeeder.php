<?php

namespace Database\Seeders;
use App\Models\Decaissement;
use App\Models\Projet;
use Illuminate\Database\Seeder;

class DecaissementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $projets = Projet::all();

        foreach($projets as $projet)
        {
            $bailleur = $projet->bailleur;

            $decaissement = $bailleur->decaissements()->create([
                'projetId' => $projet->id,
                'montant' => 10000000,
                'date' => date('y-m-d')
            ]);
        }
    }
}
