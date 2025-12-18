<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropfieldSite extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(Schema::hasTable('sites')) {
            Schema::table('sites', function (Blueprint $table) {
                if (Schema::hasColumn('sites', 'entrepriseExecutantId')) {

                    $table->dropForeign(['entrepriseExecutantId']);
			        //$table->dropForeign('sites_entrepriseExecutantId_foreign');

                    $table->dropColumn('entrepriseExecutantId');
                }
            });
        }
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
