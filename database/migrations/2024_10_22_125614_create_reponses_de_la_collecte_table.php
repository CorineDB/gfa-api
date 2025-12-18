<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReponsesDeLaCollecteTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasTable('reponses_de_la_collecte')){
            Schema::create('reponses_de_la_collecte', function (Blueprint $table) {
                $table->id();
                $table->bigInteger('programmeId')->unsigned();
                $table->foreign('programmeId')->references('id')->on('programmes')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
                    
                $table->bigInteger('soumissionId')->unsigned();
                $table->foreign('soumissionId')->references('id')->on('soumissions')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');

                $table->bigInteger('sourceDeVerificationId')->nullable()->unsigned();
                $table->foreign('sourceDeVerificationId')->references('id')->on('sources_de_verification')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
                    
                $table->bigInteger('questionId')->unsigned();
                $table->foreign('questionId')->references('id')->on('questions_de_gouvernance')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');

                $table->bigInteger('optionDeReponseId')->unsigned();
                $table->foreign('optionDeReponseId')->references('id')->on('options_de_reponse')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
                $table->enum('type', ['indicateur', 'question_operationnelle']);
                $table->integer('point');
                $table->mediumText('sourceDeVerification')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reponses_de_la_collecte');
    }
}
