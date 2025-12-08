<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCriteresDeGouvernanceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('criteres_de_gouvernance', function (Blueprint $table) {
			$table->id();
			$table->string('nom');
			$table->longText('description')->nullable();
			$table->bigInteger('principeDeGouvernanceId')->unsigned();
            $table->foreign('principeDeGouvernanceId')->references('id')->on('principes_de_gouvernance')
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
        Schema::dropIfExists('criteres_de_gouvernance');
    }
}
