<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReponsesCollecterTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasTable('reponses_collecter')){
            Schema::create('reponses_collecter', function (Blueprint $table) {
                $table->id();
                $table->bigInteger('enqueteDeCollecteId')->unsigned();
                $table->foreign('enqueteDeCollecteId')->references('id')->on('enquetes_de_collecte')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
                
                $table->bigInteger('userId')->unsigned();
                $table->foreign('userId')->references('id')->on('users')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
                $table->bigInteger('indicateurDeGouvernanceId')->unsigned();
                $table->foreign('indicateurDeGouvernanceId')->references('id')->on('indicateurs_de_gouvernance')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
                $table->bigInteger('optionDeReponseId')->unsigned();
                $table->foreign('optionDeReponseId')->references('id')->on('options_de_reponse')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
                $table->string('source')->nullable();
                $table->text('commentaire')->nullable();
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
