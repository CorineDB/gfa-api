<?php

namespace Database\Seeders;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach (range(1, 11) as $index)
        {
            if($index == 1){
                $users = User::factory()->create([
                    'nom' => 'admin',
                    'type' => 'administrateur'
                ]);
            }
           else if($index < 8 && $index>1){
            $users = User::factory()
            ->create([
                'type' => 'bailleur'

            ]);
           }else if ($index ==9){
            $users = User::factory()
            ->create([
                'nom' => 'AGETUR',
                'type' => 'mod'
            ]);
           }else if ($index ==10){
            $users = User::factory()
            ->create([
                'nom' => 'Bénin',
                'type' => 'gouvernement'
            ]);
           }
           else {
               $users = User::factory()
            ->create([
                'type' => 'institution'
            ]);
           }
        } }
    }
