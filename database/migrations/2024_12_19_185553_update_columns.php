<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('evaluations_de_gouvernance')) {
            Schema::table('evaluations_de_gouvernance', function (Blueprint $table) {
                if (Schema::hasColumn('evaluations_de_gouvernance', 'objectif_attendu')) {

                    $table->float('objectif_attendu')->default(0)->change();
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
        if (Schema::hasTable('evaluations_de_gouvernance')) {
            Schema::table('evaluations_de_gouvernance', function (Blueprint $table) {
                if (Schema::hasColumn('evaluations_de_gouvernance', 'objectif_attendu')) {

                    $table->float('objectif_attendu')->change();
                }
            });
        }
    }
}
