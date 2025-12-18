<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArchiveTachesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('archive_taches', function (Blueprint $table) {
            $table->id();
			$table->string('nom');
			$table->integer('position');
			$table->integer('poids');
			$table->bigInteger('activiteId')->unsigned();
			$table->bigInteger('parentId')->unsigned();
			$table->bigInteger('ptabScopeId')->unsigned();
			$table->longText('description');

            $table->foreign('parentId')->references('id')->on('taches')
            ->onDelete('cascade')
            ->onUpdate('cascade');
            $table->foreign('activiteId')->references('id')->on('archive_activites')
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
        Schema::dropIfExists('archive_taches');
    }
}
