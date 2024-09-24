<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEActiviteModsTable extends Migration {

	public function up()
	{
		Schema::create('e_activite_mods', function(Blueprint $table) {
			$table->id();
			$table->longText('description');
			$table->date('debut');
			$table->date('fin');
			$table->bigInteger('modId')->unsigned();
			$table->bigInteger('siteId')->unsigned();
			$table->bigInteger('bailleurId')->unsigned();
			$table->bigInteger('programmeId')->unsigned();
			$table->timestamps();
			$table->softDeletes();
		});
	}

	public function down()
	{
		Schema::dropIfExists('e_activite_mods');
	}
}