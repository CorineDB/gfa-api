<?php

namespace Database\Seeders\GFA;

use App\Models\Programme;
use Illuminate\Database\Seeder;

class ProgrammeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (Programme::count() == 0) {
            Programme::create([
                'nom' => 'Programme Redevabilité',
                'description' => 'Programme Redevabilité',
                'debut' => now(),
                'fin' => now()->addYears(5),
                'code' => 'RDV-PH3',
                'budgetNational' => 1000000000
            ]);
        }
    }
}
