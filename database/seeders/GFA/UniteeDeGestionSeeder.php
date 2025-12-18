<?php

namespace Database\Seeders\GFA;

use App\Models\Programme;
use App\Models\Role;
use App\Models\UniteeDeGestion;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UniteeDeGestionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $programme = Programme::first();

        dump($programme);

        if ($programme) {
            $unitee = UniteeDeGestion::firstOrCreate(
                ['nom' => 'Unitee de gestion du programme de redevabilité'],
                [
                    'programmeId' => $programme->id,
                ]
            );

            dump($unitee);
            // Create associated User
            $ugRole = Role::where('slug', 'unitee-de-gestion')->first();
            dump($ugRole);
            $ugUser = User::firstOrCreate(
                ['email' => 'kmarc@secuprogroup.com'],
                [
                    'nom' => 'Kokoye',
                    'prenom' => 'Marc',
                    'password' => 'Redevabilite3@',
                    'contact' => '80808080',
                    'type' => 'unitee-de-gestion',
                    'profilable_type' => UniteeDeGestion::class,
                    'profilable_id' => $unitee->id,
                    'programmeId' => $programme->id,
                    'statut' => 1,
                    'first_connexion' => false,
                    'password_update_at' => now(),
                    'link_is_valide' => true,
                    'emailVerifiedAt' => now(),
                ]
            );

            dump($ugUser);
            if ($ugRole) {
                $ugUser->roles()->syncWithoutDetaching([$ugRole->id]);
            }
            dump($ugUser->roles);
        }
    }
}
