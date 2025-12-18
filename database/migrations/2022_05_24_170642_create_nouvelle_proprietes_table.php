<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNouvelleProprietesTable extends Migration {

	public function up()
	{
		Schema::create('nouvelle_proprietes', function(Blueprint $table) {
			$table->id();
			$table->string('nom');
			$table->string('longitude');
			$table->string('latitude');
			$table->bigInteger('proprieteId')->unsigned();
			$table->timestamps();
			$table->softDeletes();
		});
	}

	public function down()
	{
		Schema::dropIfExists('nouvelle_proprietes');
	}
}