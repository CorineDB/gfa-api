<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOptionsDeReponseTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasTable('options_de_reponse')){
            Schema::create('options_de_reponse', function (Blueprint $table) {
                $table->id();
                $table->string('libelle')->unique();
                $table->string('slug')->unique();
                $table->longText('description')->nullable();
                $table->bigInteger('programmeId')->unsigned();
                $table->foreign('programmeId')->references('id')->on('programmes')
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
        Schema::dropIfExists('options_de_reponses');
    }
}
