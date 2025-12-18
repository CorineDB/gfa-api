<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEvaluationDeGouvernanceFormulairesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('evaluation_formulaires_de_gouvernance', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('evaluationDeGouvernanceId')->unsigned();
            $table->foreign('evaluationDeGouvernanceId', 'evaluation')->references('id')->on('evaluations_de_gouvernance')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->bigInteger('formulaireDeGouvernanceId')->unsigned();
            $table->foreign('formulaireDeGouvernanceId', 'formulaire')->references('id')->on('formulaires_de_gouvernance')
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
        Schema::dropIfExists('evaluation_de_gouvernance_formulaires_de_gouvernance');
    }
}
