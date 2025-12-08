<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBailleurEntrepriseExecutantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bailleur_entreprise_executants', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('bailleurId')->unsigned();
            $table->bigInteger('entrepriseExecutantId')->unsigned();
            $table->foreign('bailleurId')->references('id')->on('bailleurs')
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
        Schema::dropIfExists('bailleur_entreprise_executants');
    }
}
