<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBailleursTable extends Migration {

	public function up()
	{
		Schema::create('bailleurs', function(Blueprint $table) {
			$table->id();
			$table->string('sigle', 255)->unique();
			$table->string('pays', 255);
			$table->timestamps();
			$table->softDeletes();
		});
	}

	public function down()
	{
		Schema::dropIfExists('bailleurs');
	}
}
