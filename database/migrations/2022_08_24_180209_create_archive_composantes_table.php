<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArchiveComposantesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('archive_composantes', function (Blueprint $table) {
            $table->id();
			$table->string('nom');
			$table->integer('position');
			$table->integer('poids');
			$table->bigInteger('pret');
			$table->bigInteger('budgetNational');
			$table->longText('description')->nullable();

			$table->bigInteger('composanteId')->unsigned();

			$table->bigInteger('projetId')->unsigned();

			$table->bigInteger('parentId')->unsigned();


			$table->bigInteger('ptabScopeId')->unsigned();

            $table->foreign('parentId')->references('id')->on('composantes')
            ->onDelete('cascade')
            ->onUpdate('cascade');
            
            $table->foreign('projetId')->references('id')->on('archive_projets')
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
        Schema::dropIfExists('archive_composantes');
    }
}
