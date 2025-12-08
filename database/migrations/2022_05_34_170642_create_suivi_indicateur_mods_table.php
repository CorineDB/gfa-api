<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSuiviIndicateurModsTable extends Migration {

	public function up()
	{
		Schema::create('suivi_indicateur_mods', function(Blueprint $table) {
			$table->id();
			$table->integer('trimestre');
			$table->json('valeurRealise');
            $table->bigInteger('valeurCibleId')->unsigned();
            $table->foreign('valeurCibleId')->references('id')->on('valeur_cible_d_indicateurs')->onDelete('cascade');
			$table->timestamps();
			$table->softDeletes();
		});
	}

	public function down()
	{
		Schema::dropIfExists('suivi_indicateur_mods');
	}
}