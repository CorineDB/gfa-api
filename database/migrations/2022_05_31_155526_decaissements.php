<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


class Decaissements extends Migration

{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('decaissements', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('projetId')->unsigned();
            $table->integer('montant');
			$table->morphs('decaissementable');
            $table->date('date');
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('projetId')->references('id')->on('projets')
						->onDelete('cascade')
						->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('decaissements');
    }
}
