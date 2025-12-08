<?php

namespace Database\Seeders;
use App\Models\Permission;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $actions = [
            'Creer',
            'Modifier',
            'Supprimer',
            'Voir'
        ];
        $modules = [
            'un utilisateur',
            'un bailleur',
            'un decaissement',
            'un programme',
            'une unitee de gestion',
            'un mod',
            'une entreprise executante',
            'une mission de controle',
            'un projet',
            'une composante',
            'une activite',
            'une tache',
            'un indicateur',
            'une ong',
            'une agence',
            'une institution',
            'un ano',
            'un plan de decaissement',
            'un suivi financier',
            'un role',
            'un pap',
            'une activite environnementale',
            'un site',
            'une checklist'
        ];

        $autres = [
            'voir ptab',
            'voir ppm',
            'voir le plan de decaissement du ptab',
            'attribuer une permission',
            'retirer une permission',
            'voir le point financier des activites'
        ];

        $commonPermission = [
            'faire le suivi',
            'voir le suivi',
            'supprimer un suivi',
            'voir ptab',
            'voir ppm',
            'voir le plan de decaissement du ptab',
            'attribuer une permission',
            'retirer une permission'
        ];

        foreach($actions as $action){
            foreach($modules as $module){

                $nom = $action.' '.$module;

                $slug = str_replace(' ', '-', strtolower($nom));

                DB::table('permissions')->insert([
                    [
                        'nom' => $nom,

                        'slug' =>$slug,

                    ]

                ]);
            }
        }

        foreach($autres as $autre){
            DB::table('permissions')->insert([
                [
                    'nom' => ucfirst(str_replace('-', ' ', $autre)),

                    'slug' =>$autre,

                ]

            ]);
        }
    }
}
