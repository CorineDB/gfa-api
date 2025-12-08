<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFielsSinistreSite extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sinistres', function(Blueprint $table) {

            if (!Schema::hasColumn('sinistres', 'siteId')) {
			    $table->bigInteger('siteId')->unsigned();

                $table->foreign('siteId')->references('id')->on('sites')
						->onDelete('cascade')
						->onUpdate('cascade');
            }

            if (!Schema::hasColumn('users', 'link_is_valide')) {
                $table->dropForeign('sinistres_bailleurId_foreign');
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
