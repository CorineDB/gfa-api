<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQuestionsDePerceptionDeGouvernanceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('questions_de_perception_de_gouvernance', function (Blueprint $table) {
            $table->id();
            $table->integer('position')->default(0);
            $table->bigInteger('formulaireDePerceptionId')->unsigned();
            $table->foreign('formulaireDePerceptionId', 'qpg_form_fk')->references('id')->on('formulaires_de_perception_de_gouvernance')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->bigInteger('categorieDePerceptionDeGouvernanceId')->unsigned();
            $table->foreign('categorieDePerceptionDeGouvernanceId', 'questions_de_perception_de_gouvernance_cat_fk')->references('id')->on('categories_de_perception_de_gouvernance')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->bigInteger('questionOperationnelleId')->unsigned();
            $table->foreign('questionOperationnelleId', 'qdp_cat_fk')->references('id')->on('questions_operationnelle')
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
        Schema::dropIfExists('questions_de_perception_de_gouvernance');
    }
}
