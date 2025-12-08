<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddHasUploadPreuvesColumnToActionsAMenerTable extends Migration
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
                if(!Schema::hasColumn('actions_a_mener', 'has_upload_preuves')){
                    $table->boolean('has_upload_preuves')->default(false);
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
                if(Schema::hasColumn('actions_a_mener', 'has_upload_preuves')){
                    $table->dropColumn('has_upload_preuves');
                }
            });
        }
    }
}
