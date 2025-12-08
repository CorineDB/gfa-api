<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEvaluationPrincipesDeGouvernanceObjectifsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('evaluation_principes_de_gouvernance_objectifs');
        Schema::create('evaluation_principes_de_gouvernance_objectifs', function (Blueprint $table) {
            $table->id();

            if(!Schema::hasColumn('evaluation_principes_de_gouvernance_objectifs', 'evaluationId')){
                $table->bigInteger('evaluationId')->unsigned()->nullable();
                $table->foreign('evaluationId','evaluationId')->references('id')->on('evaluations_de_gouvernance')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
            }

            if(!Schema::hasColumn('evaluation_principes_de_gouvernance_objectifs', 'principeId')){
                $table->bigInteger('principeId')->unsigned()->nullable();
                $table->foreign('principeId','principeId')->references('id')->on('principes_de_gouvernance')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
            }

            if(!Schema::hasColumn('evaluation_principes_de_gouvernance_objectifs', 'programmeId')){
                $table->bigInteger('programmeId')->unsigned()->nullable();
                $table->foreign('programmeId','programmeId')->references('id')->on('programmes')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
            }

            $table->double('objectif_attendu', 8, 2);
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
        Schema::dropIfExists('evaluation_principes_de_gouvernance_objectifs');
    }
}
