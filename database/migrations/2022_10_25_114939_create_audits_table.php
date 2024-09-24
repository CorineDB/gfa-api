<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAuditsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('audits', function (Blueprint $table) {
            $table->id();
            $table->integer('annee');
            $table->string('entreprise');
            $table->string('entrepriseContact');
            $table->date('dateDeTransmission');
            $table->string('etat');
            $table->integer('statut');
            $table->bigInteger('projetId')->unsigned();
            $table->foreign('projetId')->references('id')->on('projets')
				  ->onDelete('cascade')
				  ->onUpdate('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('audits');
    }
}
