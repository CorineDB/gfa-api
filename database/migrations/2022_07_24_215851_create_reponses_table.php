<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReponsesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reponses', function (Blueprint $table) {
            $table->id();
            $table->string('valeur');
			$table->integer('mois');
			$table->integer('annee');
			$table->bigInteger('userId')->unsigned();
            $table->bigInteger('questionId')->unsigned();
            $table->timestamps();
            $table->foreign('userId')->references('id')->on('users')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->foreign('questionId')->references('id')->on('questions')
                ->onDelete('cascade')
                ->onUpdate('cascade');
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
        Schema::dropIfExists('reponses');
    }
}
