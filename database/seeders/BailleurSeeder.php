<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Bailleur;

class BailleurSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for ($i =1; $i<7; $i++ ){
            Bailleur::factory()->create(
                [
                    'sigle' => $i.'sigle',
                    'userId' => $i+1
                ]
                );
        }

    }
}
