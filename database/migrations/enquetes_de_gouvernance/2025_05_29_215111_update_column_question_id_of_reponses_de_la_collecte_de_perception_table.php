<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateColumnQuestionIdOfReponsesDeLaCollecteDePerceptionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reponses_de_la_collecte_de_perception', function (Blueprint $table) {
            $table->dropForeign(['optionDeReponseId']); // Drop old FK first

            $table->foreign('optionDeReponseId')->references('id')->on('options_de_reponse_gouvernance')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
