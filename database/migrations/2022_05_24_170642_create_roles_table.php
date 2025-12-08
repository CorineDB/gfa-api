<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRolesTable extends Migration {

	public function up()
	{
		Schema::create('roles', function(Blueprint $table) {
			$table->id();
			$table->string('nom', 255);
			$table->string('slug', 255);
			$table->longText('description')->nullable();
			$table->string('roleable_type', 255)->nullable();
			$table->bigInteger('roleable_id')->nullable();
			$table->timestamps();
			$table->softDeletes();
		});
	}

	public function down()
	{
		Schema::dropIfExists('roles');
	}
}