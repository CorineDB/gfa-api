<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIndicateurModsTable extends Migration {

	public function up()
	{
		Schema::create('indicateur_mods', function(Blueprint $table) {
			$table->id();
			$table->string('nom');
			$table->string('description')->nullable();
			$table->string('anneeDeBase');
			$table->string('valeurDeBase');
			$table->string('frequence');
			$table->string('source');
			$table->string('responsable');
			$table->string('definition');
			$table->bigInteger('modId')->unsigned();
			$table->bigInteger('uniteeMesureId')->unsigned();
			$table->bigInteger('categorieId')->unsigned();
			$table->timestamps();
			$table->softDeletes();
		});
	}

	public function down()
	{
		Schema::dropIfExists('indicateur_mods');
	}
}