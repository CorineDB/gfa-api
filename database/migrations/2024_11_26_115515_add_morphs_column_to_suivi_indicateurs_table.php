<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddMorphsColumnToSuiviIndicateursTable extends Migration
{
    
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('suivi_indicateurs')) {
            Schema::table('suivi_indicateurs', function (Blueprint $table) {
                // Add columns only if they don't exist
                if (!Schema::hasColumn('suivi_indicateurs', 'suivi_indicateurable_type')) {
                    $table->string('suivi_indicateurable_type')->nullable();
                }

                if (!Schema::hasColumn('suivi_indicateurs', 'suivi_indicateurable_id')) {
                    $table->bigInteger('suivi_indicateurable_id')->unsigned()->nullable();
                }

                // Check if the index exists and drop it
                $indexExists = DB::select("SHOW INDEX FROM `suivi_indicateurs` WHERE Key_name = 'suivi_indicateurs_suivi_indicateurable_type_suivi_indicateurable_id_index'");
                if (!empty($indexExists)) {
                    $table->dropIndex('suivi_indicateurs_suivi_indicateurable_type_suivi_indicateurable_id_index');
                }

                // Add a shorter name for the index
                $table->index(['suivi_indicateurable_type', 'suivi_indicateurable_id'], 'suivi_indicateurs_suivi_indicateurable_idx');
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
        if (Schema::hasTable('suivi_indicateurs')) {
            Schema::table('suivi_indicateurs', function (Blueprint $table) {
                /* // Check and drop the index if it exists
                $indexExists = DB::select("SHOW INDEX FROM `suivi_indicateurs` WHERE Key_name = 'suivi_indicateurs_suivi_indicateurable_type_suivi_indicateurable_id_index'");
                if (!empty($indexExists)) {
                    $table->dropIndex('suivi_indicateurs_suivi_indicateurable_type_suivi_indicateurable_id_index');
                } */

                // Check and drop the index if it exists
                $indexExists = DB::select("SHOW INDEX FROM `suivi_indicateurs` WHERE Key_name = 'suivi_indicateurs_suivi_indicateurable_idx'");
                if (!empty($indexExists)) {
                    $table->dropIndex('suivi_indicateurs_suivi_indicateurable_idx');
                }
                
                // Drop the columns if they exist
                if (Schema::hasColumn('suivi_indicateurs', 'suivi_indicateurable_type')) {
                    $table->dropColumn('suivi_indicateurable_type');
                }

                if (Schema::hasColumn('suivi_indicateurs', 'suivi_indicateurable_id')) {
                    $table->dropColumn('suivi_indicateurable_id');
                }
            });
        }
    }
}