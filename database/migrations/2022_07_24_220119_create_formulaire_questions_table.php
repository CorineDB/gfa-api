<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFormulaireQuestionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('formulaire_questions', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('formulaireId')->unsigned();
            $table->bigInteger('questionId')->unsigned();
            $table->integer('position');
            $table->timestamps();
            $table->foreign('formulaireId')->references('id')->on('formulaires')
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
        Schema::dropIfExists('formulaire_questions');
    }
}
