<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUniteeDeGestionsTable extends Migration {

	public function up()
	{
		Schema::create('unitee_de_gestions', function(Blueprint $table) {
			$table->id();
			$table->string('nom', 255);
			$table->bigInteger('programmeId')->unsigned();
			$table->timestamps();
			$table->softDeletes();
		});
	}

	public function down()
	{
		Schema::dropIfExists('unitee_de_gestions');
	}
}