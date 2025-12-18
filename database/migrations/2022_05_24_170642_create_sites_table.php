<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSitesTable extends Migration {

	public function up()
	{
		Schema::create('sites', function(Blueprint $table) {
			$table->id();
			$table->string('nom');
			$table->string('longitude');
			$table->string('latitude');
			$table->timestamps();
			$table->softDeletes();
		});
	}

	public function down()
	{
		Schema::dropIfExists('sites');
	}
}