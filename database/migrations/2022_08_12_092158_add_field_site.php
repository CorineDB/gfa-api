<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldSite extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sites', function(Blueprint $table) {

			$table->bigInteger('entrepriseExecutantId')->unsigned();

            $table->foreign('entrepriseExecutantId')->references('id')->on('entreprise_executants')
						->onDelete('cascade')
						->onUpdate('cascade');
		});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
