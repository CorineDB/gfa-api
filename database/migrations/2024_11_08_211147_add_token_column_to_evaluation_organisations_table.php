<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTokenColumnToEvaluationOrganisationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable("evaluation_organisations")) {
            Schema::table('evaluation_organisations', function (Blueprint $table) {

                if (!Schema::hasColumn('evaluation_organisations', 'token')) {

                    $table->string('token')->nullable();
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
        if (Schema::hasTable("evaluation_organisations")) {
            Schema::table('evaluation_organisations', function (Blueprint $table) {

                if (Schema::hasColumn('evaluation_organisations', 'token')) {

                    $table->dropColumn('token');
                }
            });
        }
    }
}
