<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePassationsTable extends Migration {

	public function up()
	{
		Schema::create('passations', function(Blueprint $table) {
			$table->id();
			$table->bigInteger('montant');
			$table->date('dateDeSignature')->nullable();
			$table->date('dateDobtention')->nullable();
			$table->date('dateDeDemarrage')->nullable();
			$table->date('datePrevisionnel')->nullable();
			$table->date('dateDobtentionAvance')->nullable();
			$table->bigInteger('montantAvance');
			$table->string('ordreDeService');
			$table->string('responsableSociologue');
			$table->bigInteger('estimation');
			$table->string('travaux');
			$table->bigInteger('entrepriseExecutantId')->unsigned();
			$table->string('passationable_type');
			$table->bigInteger('siteId')->unsigned();
			$table->bigInteger('passationable_id');
			$table->timestamps();
			$table->softDeletes();
		});
	}

	public function down()
	{
		Schema::dropIfExists('passations');
	}
}
