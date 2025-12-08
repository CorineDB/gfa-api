<?php

namespace Database\Seeders;
use App\Models\ActiviteUser;
use Illuminate\Database\Seeder;

class ActiviteUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $activiteUsers = ActiviteUser::factory()->count(16)
        ->create();
    }
}
