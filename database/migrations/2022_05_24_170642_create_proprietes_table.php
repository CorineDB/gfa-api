<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProprietesTable extends Migration {

	public function up()
	{
		Schema::create('proprietes', function(Blueprint $table) {
			$table->id();
			$table->string('nom');
			$table->string('longitude');
			$table->string('latitude');
			$table->bigInteger('montant');
			$table->date('dateDePaiement');
			$table->bigInteger('sinistreId')->unsigned();
			$table->timestamps();
			$table->softDeletes();
		});
	}

	public function down()
	{
		Schema::dropIfExists('proprietes');
	}
}
