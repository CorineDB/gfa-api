<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQuestionsDeGouvernanceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('questions_de_gouvernance', function (Blueprint $table) {
            $table->id();
			$table->enum('type', ['indicateur', 'question_operationnelle']);
            $table->bigInteger('formulaireDeGouvernanceId')->unsigned();
            $table->foreign('formulaireDeGouvernanceId')->references('id')->on('formulaires_de_gouvernance')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->bigInteger('categorieDeGouvernanceId')->unsigned();
            $table->foreign('categorieDeGouvernanceId')->references('id')->on('categories_de_gouvernance')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->bigInteger('indicateurDeGouvernanceId')->unsigned();
            $table->foreign('indicateurDeGouvernanceId')->references('id')->on('indicateurs_de_gouvernance')
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
        Schema::dropIfExists('questions_de_gouvernance');
    }
}
