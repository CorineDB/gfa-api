<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateComposantesTable extends Migration {

	public function up()
	{
		Schema::create('composantes', function(Blueprint $table) {
			$table->id();
			$table->string('nom');
			$table->integer('position');
			$table->integer('poids');
			$table->bigInteger('pret');
			$table->bigInteger('budgetNational');
			$table->integer('tepPrevu');
			$table->longText('description')->nullable();
			$table->bigInteger('projetId')->unsigned();
			$table->bigInteger('composanteId')->unsigned();
			$table->timestamps();
			$table->softDeletes();
		});
	}

	public function down()
	{
		Schema::dropIfExists('composantes');
	}
}
