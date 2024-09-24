<?php

namespace Database\Seeders;

use App\Models\Categorie;
use App\Models\Fichier;
use App\Models\Indicateur;
use App\Models\IndicateurMod;
use App\Models\Role;
use App\Models\Unitee;
use App\Models\User;
use App\Traits\Eloquents\DBStatementTrait;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    use  DBStatementTrait;
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        //$roles = ['Administrateur','Gouvernement', 'Bailleur', 'MEF', 'MCVDD', 'ACVDT', 'CAA', 'Mairie de Cotonou', "Pool d'Expert PAPC", "ABE", "DGEFC", "DGDU", "MOD","MOE", "Entreprise des traveaux", "Consultants", "Comptable", "Expert suivi évaluation"];

            $roles = [
                'Administrateur',
                'Bailleur',
                "MOD",
                "Unitee de gestion",
                "Mission de controle",
                "ONG",
                "AGENCE",
                "Entreprise executant",
                "Entreprise et institution",
                "Comptable",
                "Expert suivi évaluation"
            ];

            $roles_slugs = ['administrateur','bailleur', "mod", "unitee-de-gestion", "mission-de-controle","ong","agence", "entreprise-executant", "institution", "comptable", "expert-suivi-evaluation"];


            foreach ($roles as $key => $indice) {
                $role = Role::create([
                'nom' => $indice,
                'slug' => $roles_slugs[$key],
                'description' => $indice
                ]);
            }


        $users = [
            [
                "nom" => "BOCOGA",
                "prenom" => "Corine",
                "email" => "corinebocog@gmail.com",
                "contact" => "62004867",
                "password" => 62004867
            ],
            [
                "nom" => "CHRIS",
                "prenom" => "Amour",
                "email" => "chrisamour@gmail.com",
                "contact" => "67214237",
                "password" => 67214237
            ],
            [
                "nom" => "GANDA",
                "prenom" => "Luthe",
                "email" => "gandaluthe@gmail.com",
                "contact" => "67001237",
                "password" => 67001237
            ],
            [
                "nom" => "OLOU",
                "prenom" => "Yann",
                "email" => "yannkelly@gmail.com",
                "contact" => "67225437",
                "password" => 67225437
            ],
            [
                "nom" => "GOJANDA",
                "prenom" => "Jacob",
                "email" => "gojanda.jacob@gmail.com",
                "contact" => "95612355",
                "password" => 95612355
            ],
            [
                "nom" => "CAKPO",
                "prenom" => "Firmin",
                "email" => "cakpofirmin@gmail.com",
                "contact" => "60204867",
                "password" => 60204867
            ],
            [
                "nom" => "DOMINGO",
                "prenom" => "Isaac",
                "email" => "gojandajacob@gmail.com",
                "contact" => "96512355",
                "password" => 96512355
            ],
            [
                "nom" => "LOTO",
                "prenom" => "Fortune",
                "email" => "lotofortune@gmail.com",
                "contact" => "60701237",
                "password" => 60701237
            ],
            [
                "nom" => "AFFO",
                "prenom" => "Eric",
                "email" => "ericaffo@gmail.com",
                "contact" => "62725437",
                "password" => 62725437
            ],
            [
                "nom" => "AFFO",
                "prenom" => "Eric",
                "email" => "ericcaffo@gmail.com",
                "contact" => "62735437",
                "password" => 62735437
            ],
            [
                "nom" => "LOTOs",
                "prenom" => "Fortunes",
                "email" => "lotoforstune@gmail.com",
                "contact" => "60771237",
                "password" => 60771237
            ]
        ];

        $this->changeState(0);

        Role::first()->users()->save(new User(array_merge(
            [
                "nom" => "BOCOGA",
                "prenom" => "Corine",
                "email" => "contact@celeriteholding.com",
                "contact" => "62004867",
                "password" => "Papc@12345678"
            ], ['type' => 'administrateur' ]))
        );
        $this->changeState(1);


        /*
            User::create([
                "roleId" => Role::where('nom', 'Administrateur')->first()->id,
                "nom" => "BOCOGA",
                "prenom" => "Corine",
                "email" => "corinebocog@gmail.com",
                "contact" => "62004867",
                "password" => Hash::make("62004867")
            ]);

            User::create([
                "roleId" => Role::where('nom', 'Bailleur')->first()->id,
                "nom" => "CHRIS",
                "prenom" => "Amour",
                "email" => "chrisamour@gmail.com",
                "contact" => "67214237",
                "password" => Hash::make("67214237")
            ]);

            User::create([
                "roleId" => Role::where('nom', 'Comptable')->first()->id,
                "nom" => "OLOU",
                "prenom" => "Yann",
                "email" => "yannkelly@gmail.com",
                "contact" => "67225437",
                "password" => Hash::make("67225437")
            ]);

            User::create([
                "roleId" => Role::where('nom', 'Expert suivi évaluation')->first()->id,
                "nom" => "GOJANDA",
                "prenom" => "Jacob",
                "email" => "gojanda.jacob@gmail.com",
                "contact" => "95612355",
                "password" => Hash::make("95612355")
            ]);

        Categorie::create([
            "nom" => "Indicateurs de résultats"
        ]);

        Categorie::create([
            "nom" => "Indicateurs d'effet"
        ]);

        Unitee::create([
            "nom" => "Personne"
        ]);

        Unitee::create([
            "nom" => "Nombre"
        ]);

        Unitee::create([
            "nom" => "%"
        ]);

        Unitee::create([
            "nom" => "Km"
        ]);

        Unitee::create([
            "nom" => "Km2"
        ]);

        Unitee::create([
            "nom" => "ml"
        ]);

        Unitee::create([
            "nom" => "Millions de FCFA"
        ]);
        */

    }
}
