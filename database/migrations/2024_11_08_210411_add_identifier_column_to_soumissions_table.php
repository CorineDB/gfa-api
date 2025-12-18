<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIdentifierColumnToSoumissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable("soumissions")) {
            Schema::table('soumissions', function (Blueprint $table) {

                if (!Schema::hasColumn('soumissions', 'identifier_of_participant')) {

                    $table->string('identifier_of_participant')->nullable();
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
        if (Schema::hasTable("soumissions")) {
            Schema::table('soumissions', function (Blueprint $table) {

                if (Schema::hasColumn('soumissions', 'identifier_of_participant')) {

                    $table->dropColumn('identifier_of_participant');
                }
            });
        }
    }
}
