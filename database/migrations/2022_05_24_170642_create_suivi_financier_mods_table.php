<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSuiviFinancierModsTable extends Migration {

	public function up()
	{
		Schema::create('suivi_financier_mods', function(Blueprint $table) {
			$table->id();
			$table->smallInteger('trimestre');
			$table->string('annee');
			$table->bigInteger('decaissement');
			$table->integer('taux');
			$table->bigInteger('maitriseDoeuvreId')->unsigned();
			$table->bigInteger('siteId')->unsigned();
			$table->timestamps();
			$table->softDeletes();
		});
	}

	public function down()
	{
		Schema::dropIfExists('suivi_financier_mods');
	}
}