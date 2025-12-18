<?php

namespace Database\Seeders\GFA;

use App\Models\Organisation;
use App\Models\Programme;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class OrganisationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $programme = Programme::first();

        if ($programme) {
            $organisation = Organisation::firstOrCreate(
                ['sigle' => 'ORG-GFA'],
                [
                    'code' => 1,
                    'nom_point_focal' => 'Doe',
                    'prenom_point_focal' => 'John',
                    'contact_point_focal' => '90909090',
                    'type' => 'osc_partenaire',
                    'pays' => 'Benin',
                    'programmeId' => $programme->id,
                    // Add other required fields
                ]
            );

            // Create associated User
            $orgRole = Role::where('slug', 'organisation')->first();
            $orgUser = User::firstOrCreate(
                ['email' => 'marckokoye@live.fr'],
                [
                    'nom' => 'Organisation',
                    'prenom' => 'GFA',
                    'password' => '#Password@2025#',
                    'contact' => '90909090',
                    'type' => 'osc_partenaire',
                    'profilable_type' => Organisation::class,
                    'profilable_id' => $organisation->id,
                    'programmeId' => $programme->id,
                    'statut' => 1,
                    'link_is_valide' => true,
                    'emailVerifiedAt' => now(),
                ]
            );

            if ($orgRole) {
                $orgUser->roles()->syncWithoutDetaching([$orgRole->id]);
            }
        }
    }
}
