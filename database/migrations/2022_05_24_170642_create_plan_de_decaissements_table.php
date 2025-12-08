<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlanDeDecaissementsTable extends Migration {

	public function up()
	{
		Schema::create('plan_de_decaissements', function(Blueprint $table) {
			$table->id();
			$table->integer('trimestre');
			$table->integer('annee');
			$table->bigInteger('pret');
			$table->bigInteger('budgetNational');
			$table->bigInteger('activiteId')->unsigned();
			$table->timestamps();
			$table->softDeletes();
		});
	}

	public function down()
	{
		Schema::dropIfExists('plan_de_decaissements');
	}
}
