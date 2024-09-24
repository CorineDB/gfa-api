<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArchiveActivitesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('archive_activites', function (Blueprint $table) {
            
			$table->id();
			$table->string('nom');
			$table->integer('position')->default(0);
			$table->integer('poids');
			$table->string('type');
			$table->bigInteger('pret');
			$table->bigInteger('budgetNational');
			$table->string('description')->nullable();
			$table->bigInteger('composanteId')->unsigned();
			$table->bigInteger('parentId')->unsigned();
			$table->bigInteger('userId')->unsigned();
			$table->bigInteger('ptabScopeId')->unsigned();
            $table->foreign('userId')->references('id')->on('users')
						->onDelete('cascade')
						->onUpdate('cascade');
            $table->foreign('parentId')->references('id')->on('activites')
            ->onDelete('cascade')
            ->onUpdate('cascade');
            $table->foreign('composanteId')->references('id')->on('archive_composantes')
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
        Schema::dropIfExists('archive_activites');
    }
}
