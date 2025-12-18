<?php

namespace Database\Seeders;
use App\Models\MaitriseOeuvre;

use Illuminate\Database\Seeder;

class MaitriseOeuvreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        
        for($i=1; $i <10 ; $i++){
            if($i==2){
                $maitriseOeuvres = MaitriseOeuvre::factory()->create([
                    'id'=>$i,
                    'maitriseId' => 1
                ]);
            }else if($i!=2){
                $maitriseOeuvres = MaitriseOeuvre::factory()->create([
                    'id'=>$i,
                ]);
            }
            
        }

    }
}
