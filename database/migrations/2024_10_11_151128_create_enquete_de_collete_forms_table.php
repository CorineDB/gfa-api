<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEnqueteDeColleteFormsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('enquete_de_collete_forms', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('enqueteDeCollecteId')->unsigned();
            $table->foreign('enqueteDeCollecteId')->references('id')->on('enquetes_de_collecte')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
            $table->bigInteger('surveyFormId')->unsigned();
            $table->foreign('surveyFormId')->references('id')->on('survey_forms')
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
        Schema::dropIfExists('enquete_de_collete_forms');
    }
}
