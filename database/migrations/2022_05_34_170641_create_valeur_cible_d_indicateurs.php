<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateValeurCibleDIndicateurs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('valeur_cible_d_indicateurs', function (Blueprint $table) {
            $table->id();
            $table->integer('annee');
            $table->json('valeurCible');
            $table->morphs('cibleable');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('valeur_cible_d_indicateurs');
    }
}
