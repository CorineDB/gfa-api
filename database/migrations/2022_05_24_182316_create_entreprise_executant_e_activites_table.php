<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEntrepriseExecutantEActivitesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('entreprise_executant_e_activites', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('EActiviteId')->unsigned();
            $table->bigInteger('entrepriseExecutantId')->unsigned();
            $table->foreign('EActiviteId')->references('id')->on('e_activites')
						->onDelete('cascade')
						->onUpdate('cascade');
            $table->foreign('entrepriseExecutantId')->references('id')->on('entreprise_executants')
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
        Schema::dropIfExists('entreprise_executant_e_activites');
    }
}
