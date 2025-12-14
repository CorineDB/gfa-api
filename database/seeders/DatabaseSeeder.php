<?php

namespace Database\Seeders;

use Database\Seeders\GFA\GfaSeeder;
use Database\Seeders\GFA\ProgrammeSeeder;
use Database\Seeders\GFA\UniteeDeGestionSeeder;
use Database\Seeders\GFA\UniteeDeMesureSeeder;
use Database\Seeders\GFA\UserSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \Illuminate\Support\Facades\Artisan::call('permissions');

        // $this->call(PermissionSeeder::class);
        $this->call(AlerteConfigSeeder::class);
        $this->call(GfaSeeder::class);

        /*$this->call(ProgrammeSeeder::class);
        $this->call(UserSeeder::class);
        $this->call(UniteeDeGestionSeeder::class);
        $this->call(UniteeDeMesureSeeder::class);*/

        // OrganisationSeeder::class,
        // $this->call(CategorieSeeder::class);
        // $this->call(PermissionRoleSeeder::class);
        // $this->call([RoleSeeder::class]);
        /*$this->call([UserSeeder::class]);
        $this->call([BailleurSeeder::class]);
        $this->call([ProgrammeSeeder::class]);
        $this->call([GouvernementSeeder::class]);
        $this->call([ProjetSeeder::class]);
        $this->call([ComposanteSeeder::class]);
        $this->call([ActiviteSeeder::class]);
        $this->call([TacheSeeder::class]);
        $this->call([PlanDecaissementSeeder::class]);
        $this->call([SuiviSeeder::class]);
        $this->call([SuiviFinancierSeeder::class]);
        $this->call([DecaissementSeeder::class]); */
    }
}
