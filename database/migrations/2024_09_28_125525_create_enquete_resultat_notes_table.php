<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEnqueteResultatNotesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('enquete_resultat_notes', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('enqueteDeCollecteId')->unsigned();
            $table->foreign('enqueteDeCollecteId')->references('id')->on('enquetes_de_collecte')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
            
            $table->bigInteger('organisationId')->unsigned();
            $table->foreign('organisationId')->references('id')->on('entreprise_executants')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
            $table->bigInteger('userId')->unsigned();
            $table->foreign('userId')->references('id')->on('users')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
            $table->enum('type', ["faiblesse", "recommendation"]);
			$table->text('contenu');
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
        Schema::dropIfExists('enquete_resultat_notes');
    }
}
