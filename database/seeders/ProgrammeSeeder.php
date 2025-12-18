<?php

namespace Database\Seeders;
use App\Models\Programme;
use App\Models\MOD;
use App\Models\Bailleur;
use App\Models\Code;
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
        $programme= Programme::factory()->create();
        $mod = MOD::factory()->create();
        $programme->mods()->attach(1);
        $programme->bailleurs()->attach([1,2,3,4,5,6]);
        $programme->users()->attach(10);

        $bailleurs = Bailleur::all();

        foreach($bailleurs as $bailleur)
        {
            $code = Code::factory()->create(
                [
                    'bailleurId' => $bailleur->id,
                    'programmeId' => $programme->id,
                    'codePta' => ''.$programme->code.'.'.$bailleur->id
                ]
            );
        }

    }
}
