<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIndicateurOptionsDeReponseTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasTable("indicateur_options_de_reponse")){
            
            Schema::create('indicateur_options_de_reponse', function (Blueprint $table) {
                $table->id();
                $table->bigInteger('indicateurId')->unsigned();
                $table->bigInteger('optionId')->unsigned();
                $table->foreign('indicateurId')->references('id')->on('indicateurs_de_gouvernance')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
                $table->foreign('optionId')->references('id')->on('options_de_reponse')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
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
        Schema::table('tables', function (Blueprint $table) {
            //
        });
    }
}
