<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateESuiviActiviteModsTable extends Migration {

	public function up()
	{
		Schema::create('e_suivi_activite_mods', function(Blueprint $table) {
			$table->id();
			$table->longText('description');
			$table->integer('niveauDeMiseEnOeuvre');
			$table->bigInteger('eActiviteModId')->unsigned();
			$table->timestamps();
			$table->softDeletes();
		});
	}

	public function down()
	{
		Schema::dropIfExists('e_suivi_activite_mods');
	}
}