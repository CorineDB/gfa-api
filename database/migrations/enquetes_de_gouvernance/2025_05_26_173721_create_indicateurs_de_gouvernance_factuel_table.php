<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIndicateursDeGouvernanceFactuelTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('indicateurs_de_gouvernance_factuel', function (Blueprint $table) {
			$table->id();
            $table->longText('nom');
			$table->longText('description')->nullable();
			$table->boolean('can_have_multiple_reponse')->default(false);
			$table->bigInteger('programmeId')->unsigned();
            $table->foreign('programmeId')->references('id')->on('programmes')
				  ->onDelete('cascade')
				  ->onUpdate('cascade');
            $table->unique(['nom', 'programmeId']);
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
        Schema::dropIfExists('indicateurs_de_gouvernance_factuel');
    }
}
