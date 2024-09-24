<?php

namespace Database\Seeders;
use App\Models\IndicateurUnitee;
use Illuminate\Database\Seeder;

class IndicateurUniteeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $indicateurUnitees = IndicateurUnitee::factory()->count(7)->create();

    }
}
