<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQuestionsFactuelDeGouvernanceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('questions_factuel_de_gouvernance', function (Blueprint $table) {
            $table->id();
            $table->integer('position')->default(0);
            $table->bigInteger('formulaireFactuelId')->unsigned();
            $table->foreign('formulaireFactuelId')->references('id')->on('formulaires_factuel_de_gouvernance')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->bigInteger('categorieFactuelDeGouvernanceId')->unsigned();
            $table->foreign('categorieFactuelDeGouvernanceId', 'qfg_cat_fk')->references('id')->on('categories_factuel_de_gouvernance')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->bigInteger('indicateurFactuelDeGouvernanceId')->unsigned();
            $table->foreign('indicateurFactuelDeGouvernanceId', 'qfg_ifdgid_foreign')->references('id')->on('indicateurs_de_gouvernance_factuel')
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
        Schema::dropIfExists('questions_factuel_de_gouvernance');
    }
}
