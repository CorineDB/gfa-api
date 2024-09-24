<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCheckListsTable extends Migration {

	public function up()
	{
		Schema::create('check_lists', function(Blueprint $table) {
			$table->id();
			$table->string('nom');
			$table->string('code');
            $table->string('inputType');
			$table->bigInteger('uniteeId')->unsigned();
			$table->timestamps();
			$table->softDeletes();
		});
	}

	public function down()
	{
		Schema::dropIfExists('check_lists');
	}
}
