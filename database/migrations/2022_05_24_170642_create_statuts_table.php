<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStatutsTable extends Migration {

	public function up()
	{
		Schema::create('statuts', function(Blueprint $table) {
			$table->id();
			$table->integer('etat');
			$table->string('statuttable_type');
			$table->integer('statuttable_id');
			$table->timestamps();
			$table->softDeletes();
		});
	}

	public function down()
	{
		Schema::dropIfExists('statuts');
	}
}
