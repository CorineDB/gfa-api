<?php

namespace Database\Seeders;
use App\Models\Departement;
use Illuminate\Database\Seeder;

class DepartementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $departement = Departement::factory()->count(12)->create();
    }
}
