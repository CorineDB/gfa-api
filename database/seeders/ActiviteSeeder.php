<?php

namespace Database\Seeders;
use App\Models\Activite;
use App\Models\Composante;
use App\Models\MOD;
use App\Models\User;


use Illuminate\Database\Seeder;

class ActiviteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $souscomposantes = Composante::where('composanteId','!=',0)->get();
        $mod = MOD::find(1);
        foreach($souscomposantes as $souscomposante){
            for($i=1; $i<5; $i++){
                $activite = Activite::factory()
                ->create([
                    'nom' => 'Activite '.$i,
                    'position' => $i,
                    'composanteId' =>$souscomposante->id,
                    'userId' => $mod->id
                ]);
                $activite->structures()->attach($mod->userId,['type' =>'associer']);
                $activite->structures()->attach($mod->userId,['type' =>'responsable']);

                $statut = ['etat' => 0];
                $activite->statuts()->create($statut);

                if($activite->id % 2)
                {
                    $duree = ['debut' => '2018-0'.$i.'-01', 'fin' => '2018-0'.($i+3).'-29'];
                    $activite->durees()->create($duree);

                    $duree = ['debut' => '2019-0'.$i.'-01', 'fin' => '2019-0'.($i+3).'-29'];
                    $activite->durees()->create($duree);

                    $duree = ['debut' => '2020-0'.$i.'-01', 'fin' => '2020-0'.($i+3).'-29'];
                    $activite->durees()->create($duree);
                }

                else
                {
                    $duree = ['debut' => '2021-0'.$i.'-01', 'fin' => '2021-0'.($i+3).'-29'];
                    $activite->durees()->create($duree);

                    $duree = ['debut' => '2022-0'.$i.'-01', 'fin' => '2022-0'.($i+3).'-29'];
                    $activite->durees()->create($duree);

                    $duree = ['debut' => '2023-0'.$i.'-01', 'fin' => '2023-0'.($i+3).'-29'];
                    $activite->durees()->create($duree);
                }
            }
        }
    }
}
