<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReponseAnosTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reponse_anos', function (Blueprint $table) {
            $table->id();
            $table->text('commentaire')->nullable();
            $table->bigInteger('anoId')->unsigned();
            $table->foreign('anoId')->references('id')->on('anos')
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
        Schema::dropIfExists('reponse_anos');
    }
}
