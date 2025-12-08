<?php

namespace Database\Seeders;
use App\Models\Composante;
use App\Models\Projet;
use App\Models\Activite;
use App\Models\Tache;

use Illuminate\Database\Seeder;

class ComposanteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $projets = Projet::all();

       foreach($projets as $randomProjet) {
        for($i=1;$i<4; $i++ ){

               $composante = Composante::factory()
               ->create([
                   'nom' => 'composante'. ' '.$i,
                   'position' => $i,
                   'projetId' =>$randomProjet->id,
               ]);

               $statut = ['etat' => 0];

               $composante->statuts()->create($statut);
           }
        }

        $composantes = Composante::where('composanteId',0)->get();
        foreach($composantes as $composante){

            for($i=1;$i<3; $i++ ){

                $souscomposante = Composante::factory()
                ->create([
                    'nom' => 'la sous-composante'. ' '.$i,
                    'position' => $i,
                    'projetId' =>$composante->projetId,
                    'composanteId' => $composante->id
                ]);
            }
        }



    }
}
