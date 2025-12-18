<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCadresDeMesureRendementTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cadres_de_mesure_rendement', function (Blueprint $table) {
            $table->id();
			$table->integer('position')->default(0);
			$table->enum('type', ['impact', 'effet', 'produit']);

			$table->morphs('rendementable', 'rendement'); // ca peut etre un projet ou programme
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
