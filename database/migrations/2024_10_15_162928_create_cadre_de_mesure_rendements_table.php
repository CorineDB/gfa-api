<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCadreDeMesureRendementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cadre_de_mesure_rendements', function (Blueprint $table) {
            $table->id();
			$table->integer('position');
            $table->bigInteger('cadreDeMesureResultatId')->unsigned();
            $table->foreign('cadreDeMesureResultatId')->references('id')->on('cadre_de_mesure_resultats')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->bigInteger('indicateurId')->unsigned();
            $table->foreign('indicateurId')->references('id')->on('indicateurs')
                ->onDelete('cascade')
                ->onUpdate('cascade');
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
        Schema::dropIfExists('cadre_de_mesure_rendements');
    }
}
