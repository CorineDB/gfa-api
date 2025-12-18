<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSinistresTable extends Migration {

	public function up()
	{
		Schema::create('sinistres', function(Blueprint $table) {
			$table->id();
            $table->bigInteger('bailleurId')->unsigned();
			$table->string('nom');
			$table->string('prenoms');
			$table->string('contact');
			$table->timestamps();
			$table->softDeletes();
		});
	}

	public function down()
	{
		Schema::dropIfExists('sinistres');
	}
}
