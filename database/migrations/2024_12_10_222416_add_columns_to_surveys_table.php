<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToSurveysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(Schema::hasTable('surveys')){
            Schema::table('surveys', function (Blueprint $table) {

                // Check if the column exists
                if(!Schema::hasColumn('surveys', 'prive')){
                    $table->boolean('prive')->default(false);
                }

                // Check if the column exists
                if(!Schema::hasColumn('surveys', 'token')){
                    $table->string('token')->default(null);
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
        if(Schema::hasTable('surveys')){
            Schema::table('surveys', function (Blueprint $table) {

                // Check if the column exists
                if(Schema::hasColumn('surveys', 'prive')){
                    $table->dropColumn('prive');
                }

                // Check if the column exists
                if(Schema::hasColumn('surveys', 'token')){
                    $table->dropColumn('token');
                }
            });
        }
    }
}
