<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AaddFieldMissionDeControle extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mission_de_controles', function(Blueprint $table) {

            if (!Schema::hasColumn('mission_de_controles', 'bailleurId')) {
			    $table->bigInteger('bailleurId')->unsigned()->default(1);

                $table->foreign('bailleurId')->references('id')->on('programmes')
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
