<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArchiveProjetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('archive_projets', function (Blueprint $table) {
            $table->id();
			$table->string('nom', 255);
			$table->string('couleur', 255);
			$table->longText('description')->nullable();
			$table->string('ville');
            $table->longText('objectifGlobaux')->nullable();
			$table->bigInteger('pret');
			$table->bigInteger('budgetNational');
            $table->bigInteger('nombreEmploie')->nullable();
			$table->date('debut');
            $table->date('fin');
			$table->bigInteger('parentId')->unsigned();
			$table->bigInteger('bailleurId')->unsigned();
            $table->bigInteger('programmeId')->unsigned();
            $table->bigInteger('ptabScopeId')->unsigned();

            $table->foreign('parentId')->references('id')->on('projets')
            ->onDelete('cascade')
            ->onUpdate('cascade');
            $table->foreign('bailleurId')->references('id')->on('bailleurs')
            ->onDelete('cascade')
            ->onUpdate('cascade');
            $table->foreign('programmeId')->references('id')->on('programmes')
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
        Schema::dropIfExists('archive_projets');
    }
}
