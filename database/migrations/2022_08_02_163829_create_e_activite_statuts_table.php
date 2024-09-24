<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEActiviteStatutsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('e_activite_statuts', function (Blueprint $table) {
            $table->id();
            $table->integer('etat');
            $table->bigInteger('entrepriseId')->unsigned();
            $table->bigInteger('activiteId')->unsigned();
            $table->foreign('activiteId')->references('id')->on('e_activites')
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
        Schema::dropIfExists('e_activite_statuts');
    }
}
