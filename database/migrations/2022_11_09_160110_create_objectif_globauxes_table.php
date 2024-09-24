<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateObjectifGlobauxesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('objectif_globauxes', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->longText('description');
            $table->bigInteger('objectifable_id')->unsigned();
            $table->string('objectifable_type');
            $table->bigInteger('indicateurId')->unsigned();
            $table->foreign('indicateurId')->references('id')->on('indicateurs')
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
        Schema::dropIfExists('objectif_globauxes');
    }
}
