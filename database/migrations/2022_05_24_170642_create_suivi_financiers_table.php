<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSuiviFinanciersTable extends Migration {

	public function up()
	{
		Schema::create('suivi_financiers', function(Blueprint $table) {
			$table->id();
			$table->bigInteger('consommer');
			$table->integer('trimestre');
			$table->morphs('suivi_financierable', 'financierable');
			$table->bigInteger('activiteId')->unsigned();
			$table->integer('annee');
			$table->timestamps();
			$table->softDeletes();
		});
	}

	public function down()
	{
		Schema::dropIfExists('suivi_financiers');
	}
}