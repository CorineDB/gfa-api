<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIndicateurResponsablesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasTable('indicateur_responsables')){
            Schema::create('indicateur_responsables', function (Blueprint $table) {
                $table->id();
                $table->morphs('responsableable', 'responsable');
                $table->bigInteger('indicateurId')->unsigned();
                $table->foreign('indicateurId')->references('id')->on('indicateurs')
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
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('indicateur_responsables');
    }
}
