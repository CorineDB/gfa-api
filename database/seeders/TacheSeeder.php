<?php

namespace Database\Seeders;
use App\Models\Tache;
use App\Models\Activite;

use Illuminate\Database\Seeder;

class TacheSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $activites = Activite::all();
        foreach($activites as $activite){
            for($i=1; $i<6; $i++){
                $tache = Tache::factory()
                ->create([
                    'nom' => 'Tache '.$i,
                    'position' => $i,
                    'activiteId' =>$activite->id,
                ]);

                $statut = ['etat' => 0];
                $tache->statuts()->create($statut);

                if($activite->id % 2)
                {
                    $duree = ['debut' => '2018-0'.$i.'-01', 'fin' => '2018-0'.$i.'-28'];
                    $tache->durees()->create($duree);

                    $duree = ['debut' => '2019-0'.$i.'-01', 'fin' => '2019-0'.$i.'-28'];
                    $tache->durees()->create($duree);

                }

                else
                {
                    $duree = ['debut' => '2021-0'.$i.'-01', 'fin' => '2021-0'.$i.'-28'];
                    $tache->durees()->create($duree);

                    $duree = ['debut' => '2022-0'.$i.'-01', 'fin' => '2022-0'.$i.'-28'];
                    $tache->durees()->create($duree);

                }

            }


        }
    }
}
