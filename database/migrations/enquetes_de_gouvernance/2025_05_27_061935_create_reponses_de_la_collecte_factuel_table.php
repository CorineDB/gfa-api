<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReponsesDeLaCollecteFactuelTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reponses_de_la_collecte_factuel', function (Blueprint $table) {
            $table->id();
            $table->float('point');

            $table->boolean('preuveIsRequired')->default(false);

            $table->longText('sourceDeVerification')->nullable();

            $table->bigInteger('sourceDeVerificationId')->nullable()->unsigned();
            $table->foreign('sourceDeVerificationId')->references('id')->on('sources_de_verification')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->bigInteger('optionDeReponseId')->unsigned();
            $table->foreign('optionDeReponseId')->references('id')->on('options_de_reponse_gouvernance')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->bigInteger('questionId')->unsigned();
            $table->foreign('questionId')->references('id')->on('questions_factuel_de_gouvernance')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->bigInteger('soumissionId')->unsigned();
            $table->foreign('soumissionId')->references('id')->on('soumissions_factuel')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->bigInteger('programmeId')->unsigned();
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
        Schema::dropIfExists('reponses_de_la_collecte_factuel');
    }
}
