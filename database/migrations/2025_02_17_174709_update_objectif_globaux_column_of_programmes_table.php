<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateObjectifGlobauxColumnOfProgrammesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('programmes', function (Blueprint $table) {
            if(Schema::hasColumn('programmes', 'objectifGlobaux')){
                $table->longText('objectifGlobaux')->nullable()->change();
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
        Schema::table('programmes', function (Blueprint $table) {
            if(Schema::hasColumn('programmes', 'objectifGlobaux')){
                $table->text('objectifGlobaux')->nullable()->change();
            }

        });
    }
}
