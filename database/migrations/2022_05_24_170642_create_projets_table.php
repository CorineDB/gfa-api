<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProjetsTable extends Migration {

	public function up()
	{
		Schema::create('projets', function(Blueprint $table) {
			$table->id();
			$table->string('nom', 255);
			$table->integer('poids');
			$table->string('couleur', 255);
			$table->longText('description')->nullable();
			$table->string('ville');
            $table->longText('objectifGlobaux')->nullable();
			$table->bigInteger('pret');
			$table->bigInteger('budgetNational');
            $table->bigInteger('nombreEmploie')->nullable();
			$table->date('debut');
            $table->date('fin');
			$table->bigInteger('bailleurId')->unsigned();
            $table->bigInteger('programmeId')->unsigned();
			$table->timestamps();
			$table->softDeletes();
		});
	}

	public function down()
	{
		Schema::dropIfExists('projets');
	}
}
