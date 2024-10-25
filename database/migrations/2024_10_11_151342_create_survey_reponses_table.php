<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSurveyReponsesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('survey_reponses', function (Blueprint $table) {
            $table->id();
            $table->datetime('submitted_at');
            $table->json('response_data');
            $table->bigInteger('respond_by')->unsigned();
            $table->foreign('respond_by')->references('id')->on('users')
                ->onDelete('cascade')
                ->onUpdate('cascade');
			$table->morphs('survey_reponseable', 'reponseable');
            $table->longText('commentaire')->nullable();
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
        Schema::dropIfExists('survey_reponses');
    }
}
