<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Gouvernement;

class GouvernementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

            Gouvernement::factory()->create(
                [
                    'userId' => 10,
                    'programmeId' => 1
                ]
                );


    }
}
