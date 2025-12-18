<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProgrammesTable extends Migration {

	public function up()
	{
		Schema::create('programmes', function(Blueprint $table) {
			$table->id();
			$table->string('nom', 255);
            $table->string('code', 255);
			$table->bigInteger('budgetNational');
            $table->text('debut');
            $table->text('fin');
			$table->longText('description')->nullable();
			$table->longText('objectifGlobaux')->nullable();
			$table->timestamps();
			$table->softDeletes();
		});
	}

	public function down()
	{
		Schema::dropIfExists('programmes');
	}
}
