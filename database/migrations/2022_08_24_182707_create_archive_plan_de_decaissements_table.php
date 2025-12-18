<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArchivePlanDeDecaissementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('archive_plan_de_decaissements', function (Blueprint $table) {
            $table->id();
			$table->integer('trimestre');
			$table->integer('annee');
			$table->bigInteger('pret');
			$table->bigInteger('budgetNational');
			$table->bigInteger('parentId')->unsigned();
			$table->bigInteger('activiteId')->unsigned();
			$table->bigInteger('ptabScopeId')->unsigned();
            $table->foreign('activiteId')->references('id')->on('archive_activites')
            ->onDelete('cascade')
            ->onUpdate('cascade');

            $table->foreign('parentId')->references('id')->on('plan_de_decaissements')
            ->onDelete('cascade')
            ->onUpdate('cascade');
            $table->foreign('ptabScopeId')->references('id')->on('ptab_scopes')
            ->onDelete('cascade')
            ->onUpdate('cascade');
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
        Schema::dropIfExists('archive_plan_de_decaissements');
    }
}
