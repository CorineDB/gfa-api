<?php

namespace Database\Seeders;

use App\Models\AlerteConfig;
use Illuminate\Database\Seeder;

class AlerteConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        // frequence en jour et frequeceRapport date de chaque mois

        AlerteConfig::factory()->create([
            'module' => 'activite',
            'nombreDeJourAvant' => 2,
            'frequence' => 2,
        ]);

        AlerteConfig::factory()->create([
            'module' => 'tache',
            'nombreDeJourAvant' => 2,
            'frequence' => 2,
        ]);

        AlerteConfig::factory()->create([
            'module' => 'ano',
            'nombreDeJourAvant' => 2,
            'frequence' => 2,
        ]);

        AlerteConfig::factory()->create([
            'module' => 'suivi-indicateur',
            'debutSuivi' => '2019-01-01',
            'frequence' => 2,
        ]);

        AlerteConfig::factory()->create([
            'module' => 'suivi-financier',
            'debutSuivi' => '2019-01-01',
            'frequence' => 2,
        ]);

        AlerteConfig::factory()->create([
            'module' => 'rapport-entreprise',
            'nombreDeJourAvant' => 2,
            'frequenceRapport' => 2,
        ]);

        AlerteConfig::factory()->create([
            'module' => 'rapport-mission-de-controle',
            'nombreDeJourAvant' => 2,
            'frequenceRapport' => 2,
        ]);

        AlerteConfig::factory()->create([
            'module' => 'rapport-chef-environnemental',
            'nombreDeJourAvant' => 2,
            'frequenceRapport' => 2,
        ]);

        AlerteConfig::factory()->create([
            'module' => 'backup',
            'frequenceBackup' => 'daily',
        ]);
    }
}
