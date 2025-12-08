<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChecklistFormulairesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('checklist_formulaires', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('formulaireId')->unsigned();
            $table->bigInteger('checklistId')->unsigned();
            $table->bigInteger('activiteId')->unsigned();
            $table->integer('position');
            $table->timestamps();
            $table->foreign('formulaireId')->references('id')->on('formulaires')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->foreign('checklistId')->references('id')->on('check_lists')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->foreign('activiteId')->references('id')->on('e_activites')
                ->onDelete('cascade')
                ->onUpdate('cascade');
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
        Schema::dropIfExists('checklist_formulaires');
    }
}
