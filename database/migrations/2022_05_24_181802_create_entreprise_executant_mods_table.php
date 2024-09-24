<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEntrepriseExecutantModsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('entreprise_executant_mods', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('modId')->unsigned();
            $table->bigInteger('programmeId')->unsigned();
            $table->bigInteger('entrepriseExecutantId')->unsigned();
            $table->foreign('modId')->references('id')->on('mods')
						->onDelete('cascade')
						->onUpdate('cascade');
            $table->foreign('entrepriseExecutantId')->references('id')->on('entreprise_executants')
						->onDelete('cascade')
						->onUpdate('cascade');
            $table->foreign('programmeId')->references('id')->on('programmes')
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
        Schema::dropIfExists('entreprise_executant_mods');
    }
}
