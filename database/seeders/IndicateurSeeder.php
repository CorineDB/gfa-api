<?php

namespace Database\Seeders;
use App\Models\Indicateur;
use Illuminate\Database\Seeder;

class IndicateurSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $indicateur = Indicateur::factory()->count(7)->create();

    }
}
