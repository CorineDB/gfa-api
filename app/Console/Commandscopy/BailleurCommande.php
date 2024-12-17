<?php

namespace App\Console\Commands;

use App\Models\Bailleur;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;

class BailleurCommande extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bailleur';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $role = Role::where('slug', 'consultation-des-informations-uniquement')->first();
        $bailleur = Bailleur::where('sigle', 'IDA')->first();

        /*$user1 = User::create([
            'nom' => 'BELA Serge',
            'email' => 'sbela@worldbank.org',
            'password' => '$2y$10$d6qF.DSZyWJxfJ6Y8F8Llu5qbAdC0fU4tNMpBvpFO5/OfXC1mQgE.',
            'contact' => '22670200078',
            'type' => 'consultation-des-informations-uniquement',
            'emailVerifiedAt' => now(),
            'profilable_id' => $bailleur->id,
            'profilable_type' => get_class($bailleur),
            'programmeId' => $bailleur->user->programmeId,
            'first_connexion' => 1,
            'statut' => 1,
            'link_is_valide' => 1,
        ]);*/

        /*$user2 = User::create([
            'nom' => 'BOFFAN Louis',
            'email' => 'lboffan@worldbank.org',
            'password' => '$2y$10$cMfrjMK5W9uAkcBCjWYV/.aMiZYZCe55IDs3ttE0Zv3GbhNRP9pHu',
            'contact' => '22997227742',
            'type' => 'consultation-des-informations-uniquement',
            'emailVerifiedAt' => now(),
            'profilable_id' => $bailleur->id,
            'profilable_type' => get_class($bailleur),
            'programmeId' => $bailleur->user->programmeId,
            'first_connexion' => 1,
            'statut' => 1,
            'link_is_valide' => 1,
        ]);*/

        /*$user3 = User::create([
            'nom' => 'AZZIABI',
            'email' => 'cazzabi@worldbank.org',
            'password' => 'ODP2KPS2amzh8UnoVHYZ0uyONy/49XwWm1i7u8JOTWCTHBoo4Y3E.',
            'contact' => '00213956562',
            'type' => 'consultation-des-informations-uniquement',
            'emailVerifiedAt' => now(),
            'profilable_id' => $bailleur->id,
            'profilable_type' => get_class($bailleur),
            'programmeId' => $bailleur->user->programmeId,
            'first_connexion' => 1,
            'statut' => 1,
            'link_is_valide' => 1,
        ]);

        $user4 = User::create([
            'nom' => 'Van Van',
            'email' => 'vvuhong@worldbank.org',
            'password' => '$2y$10$cZ6BPPL02YLa9cleVm27hey1c.XRXT3nusnpXkeUny8cou.yNGMbC',
            'contact' => '12027901080',
            'type' => 'consultation-des-informations-uniquement',
            'emailVerifiedAt' => now(),
            'profilable_id' => $bailleur->id,
            'profilable_type' => get_class($bailleur),
            'programmeId' => $bailleur->user->programmeId,
            'first_connexion' => 1,
            'statut' => 1,
            'link_is_valide' => 1,
        ]);

        $user1->roles()->attach([$role->id]);
        //$user2->roles()->attach([$role->id]);
        $user3->roles()->attach([$role->id]);
        $user4->roles()->attach([$role->id]);*/

        /*$users = USer::where('profilable_type', get_class($bailleur))->where('profilable_id', $bailleur->id)->get()->pluck('email');

        dump($users);*/
    }

}
