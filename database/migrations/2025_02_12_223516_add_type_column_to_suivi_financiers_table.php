<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTypeColumnToSuiviFinanciersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(Schema::hasTable('suivi_financiers')){
            Schema::table('suivi_financiers', function (Blueprint $table) {

                if(!Schema::hasColumn('suivi_financiers', 'type')){
                    $table->string('type')->default('fond-propre')->nullable();
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
        if(Schema::hasTable('suivi_financiers')){
            Schema::table('suivi_financiers', function (Blueprint $table) {

                if(Schema::hasColumn('suivi_financiers', 'type')){
                    $table->dropColumn('type');
                }

            });
        }
    }
}
