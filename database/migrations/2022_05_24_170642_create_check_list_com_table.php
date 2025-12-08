<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCheckListComTable extends Migration {

	public function up()
	{
		Schema::create('check_list_com', function(Blueprint $table) {
			$table->id();
			$table->string('nom');
			$table->string('code');
			$table->bigInteger('uniteId')->unsigned();
			$table->bigInteger('ongComId')->unsigned();
			$table->timestamps();
			$table->softDeletes();
		});
	}

	public function down()
	{
		Schema::dropIfExists('check_list_com');
	}
}