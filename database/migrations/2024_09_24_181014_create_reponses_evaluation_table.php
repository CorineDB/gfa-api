<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReponsesEvaluationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasTable('reponses_evaluation')){
            Schema::create('reponses_evaluation', function (Blueprint $table) {
                $table->id();
                $table->jsonb('response_data');
                $table->string('source')->nullable();
                $table->bigInteger('evaluationId')->unsigned();
                $table->foreign('evaluationId')->references('id')->on('evaluations')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
                $table->bigInteger('indicateurDeGouvernanceId')->unsigned();
                $table->foreign('indicateurDeGouvernanceId')->references('id')->on('indicateurs_de_gouvernance')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
                $table->string('commentaire');
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
        Schema::dropIfExists('reponses_enquetes');
    }
}
