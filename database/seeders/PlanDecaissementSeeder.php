<?php

namespace Database\Seeders;
use App\Models\PlanDecaissement;
use App\Models\Activite;

use Illuminate\Database\Seeder;

class PlanDecaissementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //$planDecaissements = PlanDecaissement::factory()->count(8)->create();

        $activites = Activite::all();

        foreach($activites as $activite)
        {
            for($i = 1; $i < 5; $i++)
            {
                $plan = PlanDecaissement::factory()->create([
                    'trimestre' => $i,
                    'activiteId' => $activite->id
                ]);
            }
        }

    }
}
