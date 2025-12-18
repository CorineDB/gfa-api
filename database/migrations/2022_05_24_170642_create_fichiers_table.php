<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFichiersTable extends Migration {

	public function up()
	{
		Schema::create('fichiers', function(Blueprint $table) {
			$table->id();
			$table->string('nom');
			$table->string('chemin');
			$table->longText('description')->nullable();
			$table->string('source')->nullable();
			$table->string('fichiertable_type');
			$table->integer('fichiertable_id');
			$table->timestamps();
			$table->softDeletes();
		});
	}

	public function down()
	{
		Schema::dropIfExists('fichiers');
	}
}
