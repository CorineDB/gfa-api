<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePayesTable extends Migration {

	public function up()
	{
		Schema::create('payes', function(Blueprint $table) {
			$table->id();
			$table->bigInteger('montant');
			$table->bigInteger('proprieteId')->unsigned();
			$table->timestamps();
			$table->softDeletes();
		});
	}

	public function down()
	{
		Schema::dropIfExists('payes');
	}
}