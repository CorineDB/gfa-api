<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePrincipesDeGouvernanceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('principes_de_gouvernance', function (Blueprint $table) {
			$table->id();
			$table->string('nom');
			$table->longText('description')->nullable();
			$table->bigInteger('typeDeGouvernanceId')->unsigned();
            $table->foreign('typeDeGouvernanceId')->references('id')->on('types_de_gouvernance')
				  ->onDelete('cascade')
				  ->onUpdate('cascade');
			$table->timestamps();
			$table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('principes_de_gouvernance');
    }
}
