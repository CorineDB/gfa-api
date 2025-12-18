<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMiss extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(Schema::hasTable('mission_de_controles')) {
            Schema::table('mission_de_controles', function (Blueprint $table) {
                if (Schema::hasColumn('mission_de_controles', 'bailleurId')) {

			        //$table->dropForeign('mission_de_controles_bailleurId_foreign');
                    $table->dropForeign(['bailleurId']);
                    $table->dropColumn('bailleurId');
                }
            });
        }

        Schema::table('mission_de_controles', function(Blueprint $table) {

            if (!Schema::hasColumn('mission_de_controles', 'bailleurId')) {
			    $table->bigInteger('bailleurId')->unsigned()->nullable();

                $table->foreign('bailleurId')->references('id')->on('bailleurs')
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
