<?php

namespace Database\Seeders;
use App\Models\Suivi;
use App\Models\Tache;
use App\Models\Activite;
use App\Models\Composante;

use Illuminate\Database\Seeder;

class SuiviSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $taches = Tache::all();

        foreach($taches as $tache)
        {
            $suivi = Suivi::factory()->create([
                'poidsActuel' => rand(1, $tache->poids),
                'suivitable_id' => $tache->id,
                'suivitable_type' => 'App\Models\Tache',
            ]);

            if($suivi->poidsActuel == $tache->poids)
            {
                $statut = ['etat' => 2];
                $tache->statuts()->create($statut);
            }
        }

        $activites = Activite::all();

        foreach($activites as $activite)
        {
            $suivi = Suivi::factory()->create([
                'poidsActuel' => rand(1, $activite->poids),
                'suivitable_id' => $activite->id,
                'suivitable_type' => 'App\Models\Activite',
            ]);

            if($suivi->poidsActuel == $activite->poids)
            {
                $statut = ['etat' => 2];
                $activite->statuts()->create($statut);
            }
        }

        $composantes = Composante::all();

        foreach($composantes as $composante)
        {
            $suivi = Suivi::factory()->create([
                'poidsActuel' => rand(1, $composante->poids),
                'suivitable_id' => $composante->id,
                'suivitable_type' => 'App\Models\Composante',
            ]);

            if($suivi->poidsActuel == $composante->poids)
            {
                $statut = ['etat' => 2];
                $composante->statuts()->create($statut);
            }
        }
    }
}
