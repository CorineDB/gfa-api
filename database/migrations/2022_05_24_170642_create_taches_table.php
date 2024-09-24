<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTachesTable extends Migration {

	public function up()
	{
		Schema::create('taches', function(Blueprint $table) {
			$table->id();
			$table->string('nom');
			$table->integer('position');
			$table->integer('poids');
			$table->integer('tepPrevu');
			$table->bigInteger('activiteId')->unsigned();
			$table->longText('description');
			$table->timestamps();
			$table->softDeletes();
		});
	}

	public function down()
	{
		Schema::dropIfExists('taches');
	}
}
