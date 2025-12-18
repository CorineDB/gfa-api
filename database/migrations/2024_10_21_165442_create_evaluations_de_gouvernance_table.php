<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEvaluationsDeGouvernanceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasTable('evaluations_de_gouvernance')){
            Schema::create('evaluations_de_gouvernance', function (Blueprint $table) {
                $table->id();
                $table->text('intitule');
                $table->longText('description')->nullable();
                $table->float('objectif_attendu');
                $table->integer('annee_exercice');
                $table->date('debut');
                $table->date('fin');
                $table->boolean('statut')->default(0);
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
        Schema::dropIfExists('evaluations_de_gouvernance');
    }
}
