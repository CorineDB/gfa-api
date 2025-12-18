<?php

namespace Database\Seeders\GFA;

use Illuminate\Database\Seeder;

class GfaSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            UserSeeder::class,
            ProgrammeSeeder::class,
            UniteeDeMesureSeeder::class,
            UniteeDeGestionSeeder::class
            // OrganisationSeeder::class,
        ]);
    }
}
