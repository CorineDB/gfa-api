<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSuivisTable extends Migration {

	public function up()
	{
		Schema::create('suivis', function(Blueprint $table) {
			$table->id();
			$table->integer('poidsActuel');
			$table->integer('suivitable_id');
			$table->string('suivitable_type');
			$table->timestamps();
			$table->softDeletes();
		});
	}

	public function down()
	{
		Schema::dropIfExists('suivis');
	}
}
