<?php

namespace Database\Seeders;
use App\Models\SuiviIndicateur;

use Illuminate\Database\Seeder;

class SuiviIndicateurSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for($i=1; $i<8; $i++)
        $suiviIndicateur = SuiviIndicateur::factory()->create([
                'indicateurId' => $i
        ]);

    }
}
