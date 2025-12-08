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
        Schema::create('evaluation_de_gouvernance_formulaires', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('evaluationDeGouvernanceId')->unsigned();
            $table->foreign('evaluationDeGouvernanceId', 'eval_fk')->references('id')->on('evaluations_de_gouvernance')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->bigInteger('formulaireFactuelId')->unsigned()->nullable();
            $table->foreign('formulaireFactuelId', 'form_fact_fk')->references('id')->on('formulaires_factuel_de_gouvernance')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->bigInteger('formulaireDePerceptionId')->unsigned()->nullable();
            $table->foreign('formulaireDePerceptionId', 'form_perc_fk')->references('id')->on('formulaires_de_perception_de_gouvernance')
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
        Schema::dropIfExists('evaluation_formulaires_de_gouvernance');
    }
}
