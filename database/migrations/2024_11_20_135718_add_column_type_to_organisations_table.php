<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnTypeToOrganisationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable("organisations")) {
            Schema::table('organisations', function (Blueprint $table) {
                if (!Schema::hasColumn('organisations', 'type')) {
                    $table->enum('type', ['osc','osc_fosir'])->default('osc');
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
        if (Schema::hasTable("organisations")) {
            Schema::table('organisations', function (Blueprint $table) {
                if (Schema::hasColumn('organisations', 'type')) {
                    $table->dropColumn('type');
                }
            });
        }
    }
}
