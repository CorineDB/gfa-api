<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeRecommandationableColumnsNullableInRecommandationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(Schema::hasTable('recommandations')){
            Schema::table('recommandations', function (Blueprint $table) {
                // Modify the columns to be nullable

                if(Schema::hasColumn('recommandations', 'recommandationable_type')){
                    $table->string('recommandationable_type')->nullable()->change();
                }

                if(Schema::hasColumn('recommandations', 'recommandationable_id')){
                    $table->unsignedBigInteger('recommandationable_id')->nullable()->change();
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
        if(Schema::hasTable('recommandations')){
            Schema::table('recommandations', function (Blueprint $table) {
                // Revert the columns to NOT NULL
                if(Schema::hasColumn('recommandations', 'recommandationable_type')){
                    $table->string('recommandationable_type')->nullable(false)->change();
                }

                if(Schema::hasColumn('recommandations', 'recommandationable_id')){
                    $table->unsignedBigInteger('recommandationable_id')->nullable(false)->change();
                }
            });
        }
    }
}
