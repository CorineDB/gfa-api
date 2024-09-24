<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SinistreBailleurdrop extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sinistres', function(Blueprint $table) {

            if (Schema::hasColumn('sinistres', 'bailleurId')) {

                //$table->dropForeign('sinistres_bailleurId_foreign');
                $table->dropForeign(['bailleurId']);
                $table->dropColumn('bailleurId');
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
