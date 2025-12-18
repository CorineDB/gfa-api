<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeActionableColumnsNullableInActionsAMenerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(Schema::hasTable('actions_a_mener')){
            Schema::table('actions_a_mener', function (Blueprint $table) {
                // Modify the columns to be nullable

                if(Schema::hasColumn('actions_a_mener', 'actionable_type')){
                    $table->string('actionable_type')->nullable()->change();
                }

                if(Schema::hasColumn('actions_a_mener', 'actionable_id')){
                    $table->unsignedBigInteger('actionable_id')->nullable()->change();
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
        if(Schema::hasTable('actions_a_mener')){
            Schema::table('actions_a_mener', function (Blueprint $table) {
                // Revert the columns to NOT NULL
                if(Schema::hasColumn('actions_a_mener', 'actionable_type')){
                    $table->string('actionable_type')->nullable(false)->change();
                }

                if(Schema::hasColumn('actions_a_mener', 'actionable_id')){
                    $table->unsignedBigInteger('actionable_id')->nullable(false)->change();
                }
            });
        }
    }
}
