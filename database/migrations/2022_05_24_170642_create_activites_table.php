<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateActivitesTable extends Migration {

	public function up()
	{
		Schema::create('activites', function(Blueprint $table) {
			$table->id();
			$table->string('nom');
			$table->integer('position');
			$table->integer('poids');
			$table->string('type');
			$table->bigInteger('pret');
			$table->bigInteger('budgetNational');
			$table->integer('tepPrevu');
			$table->string('description')->nullable();
			$table->bigInteger('composanteId')->unsigned();
			$table->bigInteger('userId')->unsigned();
			$table->timestamps();
			$table->softDeletes();
		});
	}

	public function down()
	{
		Schema::dropIfExists('activites');
	}
}
