<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCadreDeMesureResultatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cadre_de_mesure_resultats', function (Blueprint $table) {
            $table->id();
			$table->integer('position')->default(0);
			$table->enum('type', ['impact', 'effet', 'produit']);
            $table->bigInteger('cadreDeMesureId')->unsigned();
            $table->foreign('cadreDeMesureId')->references('id')->on('cadres_de_mesure')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->bigInteger('resultatCadreDeRendementId')->unsigned();
            $table->foreign('resultatCadreDeRendementId')->references('id')->on('resultats_cadre_de_rendement')
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
        Schema::dropIfExists('cadre_de_mesure_resultats');
    }
}
