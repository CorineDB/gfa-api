<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddfieldFormulaire extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('formulaires', function(Blueprint $table) {

			$table->longText('json');

            $table->bigInteger('programmeId')->unsigned();

            $table->foreign('programmeId')->references('id')->on('programmes')
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
