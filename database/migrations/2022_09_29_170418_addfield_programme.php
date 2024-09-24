<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddfieldProgramme extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('indicateurs', function(Blueprint $table) {

            if (!Schema::hasColumn('indicateurs', 'programmeId')) {
			    $table->bigInteger('programmeId')->unsigned()->nullable();

                $table->foreign('programmeId')->references('id')->on('programmes')
						->onDelete('cascade')
						->onUpdate('cascade');
            }
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
