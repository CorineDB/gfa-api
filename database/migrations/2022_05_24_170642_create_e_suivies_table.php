<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateESuiviesTable extends Migration {

	public function up()
	{
		Schema::create('e_suivies', function(Blueprint $table) {
			$table->id();
			$table->string('valeur');
			$table->integer('mois');
			$table->integer('annee');
			$table->bigInteger('userId')->unsigned();
            $table->bigInteger('siteId')->unsigned();
            $table->bigInteger('checkListId')->unsigned();
            $table->bigInteger('activiteId')->unsigned();
            $table->bigInteger('missionDeControleId')->unsigned();
            $table->bigInteger('entrepriseExecutantId')->unsigned();
			$table->timestamps();
			$table->softDeletes();
		});
	}

	public function down()
	{
		Schema::dropIfExists('e_suivies');
	}
}
