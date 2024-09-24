<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArchiveDecaissementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('archive_decaissements', function (Blueprint $table) {
            $table->id();
            $table->integer('montant');
            $table->date('date');
			$table->morphs('morphable');
            $table->bigInteger('projetId')->unsigned();
			$table->bigInteger('userId')->unsigned();

            $table->foreign('projetId')->references('id')->on('archive_projets')
						->onDelete('cascade')
						->onUpdate('cascade');

            $table->foreign('userId')->references('id')->on('users')
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
        Schema::dropIfExists('archive_decaissements');
    }
}
