<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOngComTable extends Migration {

	public function up()
	{
		Schema::create('ong_com', function(Blueprint $table) {
			$table->id();
			$table->timestamps();
			$table->softDeletes();
		});
	}

	public function down()
	{
		Schema::dropIfExists('ong_com');
	}
}
