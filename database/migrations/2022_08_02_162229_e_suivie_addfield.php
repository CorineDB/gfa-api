<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ESuivieAddfield extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('e_suivies')) {
            Schema::table('e_suivies', function(Blueprint $table) {

                if (Schema::hasColumn('e_suivies', 'missionDeControleId')) {
                    $table->dropForeign(['missionDeControleId']);
                    //$table->dropForeign('e_suivies_missionDeControleId_foreign');
                    $table->dropColumn('missionDeControleId');
                }
                if (!Schema::hasColumn('e_suivies', 'auteurable_type')) {
                    $table->string('auteurable_type');
                }
                if (!Schema::hasColumn('e_suivies', 'auteurable_id')) {
                    $table->bigInteger('auteurable_id')->unsigned();
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
        if (Schema::hasTable('e_suivies')) {
            Schema::table('e_suivies', function(Blueprint $table) {

                if (Schema::hasColumn('e_suivies', 'auteurable_type')) {
                    $table->dropColumn('auteurable_type');
                }
                if (Schema::hasColumn('e_suivies', 'auteurable_id')) {
                    $table->dropColumn('auteurable_id');
                }
            });
        }

    }
}
