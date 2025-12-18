<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReponsesDeLaCollecteDePerceptionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reponses_de_la_collecte_de_perception', function (Blueprint $table) {
            $table->id();
            $table->float('point');
            $table->bigInteger('programmeId')->unsigned();
            $table->foreign('programmeId')->references('id')->on('programmes')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->bigInteger('soumissionId')->unsigned();
            $table->foreign('soumissionId')->references('id')->on('soumissions_de_perception')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->bigInteger('questionId')->unsigned();
            $table->foreign('questionId')->references('id')->on('questions_de_perception_de_gouvernance')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->bigInteger('optionDeReponseId')->unsigned();
            $table->foreign('optionDeReponseId')->references('id')->on('options_de_reponse')
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
        Schema::dropIfExists('reponses_de_la_collecte_de_perception');
    }
}
