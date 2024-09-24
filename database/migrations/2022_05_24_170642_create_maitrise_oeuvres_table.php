<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMaitriseOeuvresTable extends Migration {

	public function up()
	{
		Schema::create('maitrise_oeuvres', function(Blueprint $table) {
			$table->id();
			$table->string('nom');
			$table->bigInteger('estimation');
			$table->bigInteger('engagement');
			$table->string('reference');
			$table->bigInteger('bailleurId')->unsigned();
			$table->timestamps();
			$table->softDeletes();
		});
	}

	public function down()
	{
		Schema::dropIfExists('maitrise_oeuvres');
	}
}