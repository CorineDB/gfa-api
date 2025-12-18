<?php

namespace Database\Seeders;
use App\Models\Unitee;

use Illuminate\Database\Seeder;

class UniteeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $unit = ['Personne', '%', 'ml', 'hl'];
        for($i=0; $i<4;$i++)
        $unitee = Unitee::factory()->create([
            'nom' => $unit[$i],
        ]);
    }
}
