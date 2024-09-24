<?php

namespace Database\Seeders;
use App\Models\Projet;
use App\Models\Bailleur;
use App\Models\Code;

use Illuminate\Database\Seeder;

class ProjetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $bailleurs = Bailleur::all();

        foreach($bailleurs as $bailleur)
        {
            $code = Code::where('bailleurId', $bailleur->id)->where('programmeId', 1)->first();

            $projet = Projet::factory()->create([
                'bailleurId' => $bailleur->id,
            ]);

            $statut = ['etat' => 0];

            $projet->statuts()->create($statut);
        }
    }
}
