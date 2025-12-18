<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEActivitesTable extends Migration {

	public function up()
	{
		Schema::create('e_activites', function(Blueprint $table) {
			$table->id();
			$table->string('nom');
			$table->string('code');
			$table->date('debut');
			$table->date('fin');
			$table->bigInteger('programmeId')->unsigned();
			$table->timestamps();
			$table->softDeletes();
		});
	}

	public function down()
	{
		Schema::dropIfExists('e_activites');
	}
}