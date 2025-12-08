<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateComSuivisTable extends Migration {

	public function up()
	{
		Schema::create('com_suivis', function(Blueprint $table) {
			$table->id();
			$table->string('valeur');
			$table->integer('mois');
			$table->integer('annee');
			$table->string('responsable_enquete');
			$table->bigInteger('checkListComId')->unsigned();
			$table->timestamps();
			$table->softDeletes();
		});
	}

	public function down()
	{
		Schema::dropIfExists('com_suivis');
	}
}