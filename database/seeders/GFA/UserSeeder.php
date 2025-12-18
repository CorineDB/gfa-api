<?php

namespace Database\Seeders\GFA;

use App\Models\Programme;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class UserSeeder extends Seeder
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
        // Admin User
        $adminRole = Role::where('slug', 'administrateur')->first();
        dump($adminRole);
        Schema::disableForeignKeyConstraints();
        $admin = User::firstOrCreate(
            ['email' => 'contact@celeriteholding.com'],
            [
                'nom' => 'Admin',
                'prenom' => 'GFA',
                'password' => 'password',
                'contact' => '00000000',
                'type' => 'administrateur',
                'programmeId' => null,
                'statut' => 1,
                'link_is_valide' => true,
                'emailVerifiedAt' => now(),
            ]
        );
        Schema::enableForeignKeyConstraints();
        dump($admin);

        if ($adminRole) {
            $admin->roles()->syncWithoutDetaching([$adminRole->id]);

            dump($admin->roles);
        }
    }
}
