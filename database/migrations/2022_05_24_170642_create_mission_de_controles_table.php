<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMissionDeControlesTable extends Migration {

	public function up()
	{
		Schema::create('mission_de_controles', function(Blueprint $table) {
			$table->id();
			$table->timestamps();
			$table->softDeletes();
		});
	}

	public function down()
	{
		Schema::dropIfExists('mission_de_controles');
	}
}
