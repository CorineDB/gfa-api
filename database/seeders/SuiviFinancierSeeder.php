<?php

namespace Database\Seeders;
use App\Models\SuiviFinancier;
use App\Models\Activite;

use Illuminate\Database\Seeder;

class SuiviFinancierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $activites = Activite::all();

        foreach($activites as $activite)
        {
            for($i = 1; $i < 5; $i++)
            {
                $bailleur = $activite->composante->projet->bailleur;

                $suivi = $bailleur->suiviFinanciers()->create([
                    'trimestre' => $i,
                    'activiteId' => $activite->id,
                    'consommer' => 1000000,
                    'annee' => 2022
                ]);
            }
        }
    }
}
