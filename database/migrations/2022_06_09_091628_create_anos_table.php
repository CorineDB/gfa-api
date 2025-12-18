<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAnosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('anos', function (Blueprint $table) {
            $table->id();
            $table->string('dossier');
            $table->bigInteger('auteurId')->unsigned();
            $table->bigInteger('bailleurId')->unsigned();
            $table->bigInteger('typeId')->unsigned();
            $table->string('destinataire');
            $table->date('dateDeSoumission');
            $table->date('dateDeReponse')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('bailleurId')->references('id')->on('bailleurs')
						->onDelete('cascade')
						->onUpdate('cascade');
            $table->foreign('auteurId')->references('id')->on('users')
						->onDelete('cascade')
						->onUpdate('cascade');
            $table->foreign('typeId')->references('id')->on('anos')
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
        Schema::dropIfExists('anos');
    }
}
